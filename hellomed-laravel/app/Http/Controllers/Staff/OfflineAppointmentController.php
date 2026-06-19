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
        $allDepartments = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_departments(:cursor); END;", [], \App\Models\Department::class);
        $allDoctors = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_all_active_doctors(:cursor); END;", [], \App\Models\Doctor::class);
        
        $departments = $allDepartments->filter(function($dept) use ($allDoctors) {
            $doctors = $allDoctors->where('department_id', $dept->id);
            if ($doctors->count() > 0) {
                $dept->setRelation('doctors', $doctors);
                return true;
            }
            return false;
        })->values();

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
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other',
            'height_cm' => 'nullable|numeric|min:30|max:300',
            'weight_kg' => 'nullable|numeric|min:1|max:500',
            'known_conditions' => 'nullable|string|max:4000',
            'allergies' => 'nullable|string|max:4000',
            'medical_notes' => 'nullable|string|max:4000',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Check if user exists via PL/SQL
            $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_email(:email, :cursor); END;", ['email' => $validated['patient_email']], \App\Models\User::class)->first();
            
            $userId = $user ? $user->id : null;
            $passwordStr = Hash::make('password123');

            if (!$user) {
                // Create user via PL/SQL
                $createParams = [
                    'name' => $validated['patient_name'],
                    'email' => $validated['patient_email'],
                    'password' => $passwordStr,
                    'out_user_id' => null
                ];
                \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_patient_user(:name, :email, :password, :out_user_id); END;", $createParams);
                $userId = $createParams['out_user_id'];
            }

            // Update patient profile via PL/SQL
            $profileParams = [
                'user_id' => $userId,
                'dob' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'height' => $validated['height_cm'] ?? null,
                'weight' => $validated['weight_kg'] ?? null,
                'conditions' => $validated['known_conditions'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'notes' => $validated['medical_notes'] ?? null
            ];
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_patient_profile(:user_id, TO_DATE(:dob, 'YYYY-MM-DD'), :gender, :height, :weight, :conditions, :allergies, :notes); END;", $profileParams);

            $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $validated['doctor_id']], \App\Models\Doctor::class)->first();
            $scheduledForStr = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['scheduled_time'])->format('Y-m-d H:i:s');
            
            $apptParams = [
                'user_id' => $userId,
                'doctor_id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'service_id' => null,
                'patient_name' => $validated['patient_name'],
                'patient_email' => $validated['patient_email'],
                'patient_phone' => $validated['patient_phone'],
                'service_mode' => 'offline',
                'scheduled_for' => $scheduledForStr,
                'reason' => $validated['reason'],
                'out_appointment_id' => null
            ];

            try {
                \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_appointments.book_appointment(:user_id, :doctor_id, :department_id, :service_id, :patient_name, :patient_email, :patient_phone, :service_mode, :scheduled_for, :reason, :out_appointment_id); END;", $apptParams);
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Time conflict')) {
                    throw ValidationException::withMessages([
                        'scheduled_time' => 'Time conflict detected for the selected slot.',
                    ]);
                }
                throw $e;
            }

            $appointmentId = $apptParams['out_appointment_id'];

            // Confirm offline appointment directly
            $updateParams = [
                'id' => $appointmentId,
                'status' => 'confirmed'
            ];
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_appointments.update_appointment_status(:id, :status); END;", $updateParams);

            // Fetch appointment to get token number
            $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $appointmentId], \App\Models\Appointment::class)->first();

            $successMsg = 'Offline appointment successfully booked for ' . $validated['patient_name'] . '. Password for new account (if any) is password123. Token: ' . $appointment->token_number;
            return redirect()->route('admin.appointments.index')->with('success', $successMsg);
        });
    }
}
