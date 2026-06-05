<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Payment;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function create(Doctor $doctor)
    {
        return view('appointments.create', compact('doctor'));
    }

    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();

        $appointment = DB::transaction(function () use ($request, $validated) {
            $doctor = Doctor::query()->lockForUpdate()->findOrFail($validated['doctor_id']);
            $scheduledFor = Carbon::parse($validated['scheduled_for']);

            $alreadyBooked = Appointment::query()
                ->where('doctor_id', $doctor->id)
                ->where('scheduled_for', $scheduledFor)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($alreadyBooked) {
                throw ValidationException::withMessages([
                    'scheduled_for' => 'The selected time slot is already booked.',
                ]);
            }

            $pdo = \Illuminate\Support\Facades\DB::getPdo();
            $stmt = $pdo->prepare('BEGIN pkg_appointments.book_appointment(:user_id, :doctor_id, :department_id, :service_id, :patient_name, :patient_email, :patient_phone, :service_mode, :scheduled_for, :reason, :appointment_id); END;');
            
            $userId = $request->user()?->id;
            $doctorId = $validated['doctor_id'];
            $departmentId = $validated['department_id'];
            $serviceId = $validated['service_id'] ?? null;
            $patientName = $validated['patient_name'];
            $patientEmail = $validated['patient_email'];
            $patientPhone = $validated['patient_phone'];
            $serviceMode = $validated['service_mode'];
            $scheduledForStr = $scheduledFor->format('Y-m-d H:i:s');
            $reason = $validated['reason'];
            $appointmentId = null;

            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->bindParam(':department_id', $departmentId);
            $stmt->bindParam(':service_id', $serviceId);
            $stmt->bindParam(':patient_name', $patientName);
            $stmt->bindParam(':patient_email', $patientEmail);
            $stmt->bindParam(':patient_phone', $patientPhone);
            $stmt->bindParam(':service_mode', $serviceMode);
            $stmt->bindParam(':scheduled_for', $scheduledForStr);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':appointment_id', $appointmentId, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

            $stmt->execute();

            $appointment = Appointment::query()->findOrFail($appointmentId);
            
            // Set additional attributes not handled by the basic PL/SQL proc
            $appointment->payment_status = $request->input('payment_method') && $request->input('payment_method') !== 'none' ? 'pending' : 'not_required';
            $appointment->save();

            if ($request->filled('payment_method') && $request->input('payment_method') !== 'none') {
                $amount = $request->input('service_mode') === 'online'
                    ? ($appointment->doctor->online_fee ?? $appointment->doctor->consultation_fee)
                    : ($appointment->doctor->offline_fee ?? $appointment->doctor->consultation_fee);

                Payment::query()->create([
                    'appointment_id' => $appointment->id,
                    'user_id' => $request->user()?->id,
                    'method' => $request->input('payment_method'),
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            }

            return $appointment;
        });

        NotificationService::sendEmail(
            $appointment->patient_email,
            'HelloMed Appointment Request Submitted',
            "Hello {$appointment->patient_name}, your appointment request with {$appointment->doctor->name} on {$appointment->scheduled_for?->format('M d, Y h:i A')} has been submitted.",
            'appointment.request.submitted',
            $request->user(),
            $appointment
        );

        $adminRecipients = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->pluck('email')
            ->all();

        if ($adminRecipients !== []) {
            foreach ($adminRecipients as $recipient) {
                NotificationService::sendEmail(
                    $recipient,
                    'New Appointment Request',
                    "New appointment request: {$appointment->patient_name} with {$appointment->doctor->name} on {$appointment->scheduled_for?->format('M d, Y h:i A')}.",
                    'appointment.request.admin_alert',
                    null,
                    $appointment
                );
            }
        }

        AuditLogger::log('appointment.created', $appointment, [], [
            'status' => $appointment->status,
            'service_mode' => $appointment->service_mode,
        ]);

        return redirect()
            ->route('home')
            ->with('status', 'Appointment request submitted successfully.');
    }
}
