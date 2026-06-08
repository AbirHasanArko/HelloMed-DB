<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfflineAppointmentController extends Controller
{
    public function create()
    {
        $departments = Department::whereHas('doctors', function ($query) {
            $query->where('is_active', true);
        })->with(['doctors' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        return view('staff.offline-appointments.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_email' => 'required|email|max:255',
            'patient_phone' => 'required|string|max:255',
            'doctor_id' => 'required|exists:doctors,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:1000',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $user = User::where('email', $validated['patient_email'])->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $validated['patient_name'],
                    'email' => $validated['patient_email'],
                    'password' => Hash::make('password123'),
                    'role' => 'patient',
                ]);
            }

            $doctor = Doctor::findOrFail($validated['doctor_id']);
            $scheduledFor = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time']);
            $scheduledForStr = $scheduledFor->format('Y-m-d H:i:s');
            
            $pdo = \Illuminate\Support\Facades\DB::getPdo();
            $stmt = $pdo->prepare('BEGIN pkg_appointments.book_appointment(:user_id, :doctor_id, :department_id, :service_id, :patient_name, :patient_email, :patient_phone, :service_mode, :scheduled_for, :reason, :appointment_id); END;');
            
            $userId = $user->id;
            $doctorId = $doctor->id;
            $departmentId = $doctor->department_id;
            $serviceId = null;
            $patientName = $validated['patient_name'];
            $patientEmail = $validated['patient_email'];
            $patientPhone = $validated['patient_phone'];
            $serviceMode = 'offline';
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

            try {
                $stmt->execute();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Time conflict')) {
                    throw ValidationException::withMessages([
                        'scheduled_time' => 'Time conflict detected for the selected slot.',
                    ]);
                }
                throw $e;
            }

            // The package creates it as 'pending', but offline bookings are usually confirmed immediately
            $appointment = Appointment::findOrFail($appointmentId);
            $appointment->status = 'confirmed';
            $appointment->save();

            return redirect()->route('admin.appointments.index')
                ->with('success', 'Offline appointment successfully booked for ' . $user->name . '. Password for new account (if any) is password123. Token: ' . $appointment->token_number);
        });
    }
}
