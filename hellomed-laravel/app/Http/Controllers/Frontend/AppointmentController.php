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
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function create($doctor)
    {
        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('
            BEGIN
                OPEN :cursor FOR
                    SELECT d.*, u.name as user_name, u.email, dept.name as department_name 
                    FROM doctors d
                    JOIN users u ON d.user_id = u.id
                    JOIN departments dept ON d.department_id = dept.id
                    WHERE d.slug = :slug;
            END;
        ');
        $stmt->bindParam(':slug', $doctor);
        $cursor = null;
        $stmt->bindParam(':cursor', $cursor, \PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($cursor);
        
        $row = oci_fetch_assoc($cursor);
        abort_unless($row, 404);
        
        $lowerRow = [];
        foreach ($row as $k => $v) {
            if (is_object($v) && (get_class($v) === 'OCILob' || get_class($v) === 'OCI-Lob' || method_exists($v, 'load'))) {
                $v = $v->load();
            }
            $lowerRow[strtolower($k)] = $v;
        }
        oci_free_statement($cursor);
        
        $hydratedDoctor = \App\Models\Doctor::hydrate([$lowerRow])->first();
        $department = new \App\Models\Department();
        $department->name = $hydratedDoctor->department_name;
        $department->id = $hydratedDoctor->department_id;
        $hydratedDoctor->setRelation('department', $department);
        $hydratedDoctor->name = $hydratedDoctor->user_name;

        return view('appointments.create', ['doctor' => $hydratedDoctor]);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();

        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $validated['doctor_id']], \App\Models\Doctor::class)->firstOrFail();
        $scheduledFor = Carbon::parse($validated['scheduled_for']);
        $scheduledForStr = $scheduledFor->format('Y-m-d H:i:s');

        $params = [
            'doctor_id' => $doctor->id,
            'scheduled_for' => $scheduledForStr,
            'exclude_id' => 0,
            'count' => null
        ];
        
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_reads.check_slot_availability(:doctor_id, TO_TIMESTAMP(:scheduled_for, 'YYYY-MM-DD HH24:MI:SS'), :exclude_id, :count); END;", $params);

        if ($params['count'] > 0) {
            throw ValidationException::withMessages([
                'scheduled_for' => 'The selected time slot is already booked.',
            ]);
        }

        $bookParams = [
            'user_id' => $request->user()?->id,
            'doctor_id' => $validated['doctor_id'],
            'department_id' => $validated['department_id'],
            'service_id' => $validated['service_id'] ?? null,
            'patient_name' => $validated['patient_name'],
            'patient_email' => $validated['patient_email'],
            'patient_phone' => $validated['patient_phone'],
            'service_mode' => $validated['service_mode'],
            'scheduled_for' => $scheduledForStr,
            'reason' => $validated['reason'],
            'appointment_id' => null
        ];

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_appointments.book_appointment(:user_id, :doctor_id, :department_id, :service_id, :patient_name, :patient_email, :patient_phone, :service_mode, TO_TIMESTAMP(:scheduled_for, 'YYYY-MM-DD HH24:MI:SS'), :reason, :appointment_id); END;", $bookParams);

        $appointmentId = $bookParams['appointment_id'];

        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $appointmentId], \App\Models\Appointment::class)->firstOrFail();
        
        $paymentStatus = $request->input('payment_method') && $request->input('payment_method') !== 'none' ? 'pending' : 'not_required';
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_appt_payment_status(:id, :status); END;", ['id' => $appointment->id, 'status' => $paymentStatus]);
        $appointment->payment_status = $paymentStatus;

        if ($request->filled('payment_method') && $request->input('payment_method') !== 'none') {
            $amount = $request->input('service_mode') === 'online'
                ? ($doctor->online_fee ?? $doctor->consultation_fee)
                : ($doctor->offline_fee ?? $doctor->consultation_fee);

            $paymentParams = [
                'appointment_id' => $appointment->id,
                'user_id' => $request->user()?->id,
                'method' => $request->input('payment_method'),
                'amount' => $amount,
                'status' => 'pending',
                'id' => null
            ];
            
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_payment(:appointment_id, :user_id, :method, :amount, :status, :id); END;", $paymentParams);
        }

        $appointment->setRelation('doctor', $doctor);

        NotificationService::sendEmail(
            $appointment->patient_email,
            'HelloMed Appointment Request Submitted',
            "Hello {$appointment->patient_name}, your appointment request with {$appointment->doctor->name} on {$appointment->scheduled_for?->format('M d, Y h:i A')} has been submitted.",
            'appointment.request.submitted',
            $request->user(),
            $appointment
        );

        $adminUsers = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_admin_staff_users(:cursor); END;", [], \App\Models\User::class);
        $adminRecipients = $adminUsers->pluck('email')->all();

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
