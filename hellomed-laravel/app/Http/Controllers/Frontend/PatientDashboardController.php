<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PatientDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = \App\Helpers\OracleHelper::fetchCursor("BEGIN OPEN :cursor FOR SELECT * FROM patient_profiles WHERE user_id = :user_id; END;", ['user_id' => $user->id], \App\Models\PatientProfile::class)->first();

        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $params = [
            'user_id' => $user->id,
            'limit' => $perPage,
            'offset' => $offset,
            'out_total' => null
        ];

        $appointmentsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_patient_appts(:user_id, :limit, :offset, :total, :cursor); END;", $params, \App\Models\Appointment::class);
        $total = \App\Helpers\OracleHelper::$lastOutParams['out_total'];

        foreach ($appointmentsCollection as $appt) {
            $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $appt->doctor_id], \App\Models\Doctor::class)->first();
            if ($doctor) {
                $department = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_department_by_id(:department_id, :cursor); END;", ['department_id' => $doctor->department_id], \App\Models\Department::class)->first();
                $doctor->setRelation('department', $department);
            }
            $appt->setRelation('doctor', $doctor);
            
            $payments = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_payments(:appointment_id, :cursor); END;", ['appointment_id' => $appt->id], \App\Models\Payment::class);
            $appt->setRelation('payments', $payments);
        }

        $appointments = new \Illuminate\Pagination\LengthAwarePaginator($appointmentsCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('patient.appointments', [
            'profile' => $profile,
            'appointments' => $appointments,
        ]);
    }

    public function show($id): View
    {
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:appointment_id, :cursor); END;", ['appointment_id' => $id], \App\Models\Appointment::class)->firstOrFail();
        
        $this->authorize('view', $appointment);

        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $appointment->doctor_id], \App\Models\Doctor::class)->first();
        if ($doctor) {
            $department = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_department_by_id(:department_id, :cursor); END;", ['department_id' => $doctor->department_id], \App\Models\Department::class)->first();
            $doctor->setRelation('department', $department);
        }
        $appointment->setRelation('doctor', $doctor);
        
        $payments = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_payments(:appointment_id, :cursor); END;", ['appointment_id' => $appointment->id], \App\Models\Payment::class);
        $appointment->setRelation('payments', $payments);
        
        $prescriptionItems = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appt_prescription_items(:id, :cursor); END;", ['id' => $appointment->id], \App\Models\AppointmentPrescriptionItem::class);
        foreach ($prescriptionItems as $item) {
            if ($item->medicine_id) {
                $medicine = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_by_id(:id, :cursor); END;", ['id' => $item->medicine_id], \App\Models\Medicine::class)->first();
                $item->setRelation('medicine', $medicine);
            }
        }
        $appointment->setRelation('prescriptionItems', $prescriptionItems);
        
        $chatMessages = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_chat_messages(:appointment_id, :cursor); END;", ['appointment_id' => $appointment->id], \App\Models\AppointmentChatMessage::class);
        foreach ($chatMessages as $msg) {
            $msgUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $msg->user_id], \App\Models\User::class)->first();
            $msg->setRelation('user', $msgUser);
        }
        $appointment->setRelation('chatMessages', $chatMessages);

        return view('patient.appointment-show', compact('appointment'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:appointment_id, :cursor); END;", ['appointment_id' => $id], \App\Models\Appointment::class)->firstOrFail();
        
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'action' => ['required', 'in:cancel,reschedule'],
            'scheduled_for' => ['nullable', 'date', 'after:now'],
        ]);

        if ($validated['action'] === 'cancel') {
            if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
                return back()->withErrors([
                    'action' => 'Only pending or confirmed appointments can be cancelled.',
                ]);
            }

            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_appointments.update_status(:appointment_id, :status); END;", ['appointment_id' => $appointment->id, 'status' => 'cancelled']);

            return back()->with('status', 'Appointment cancelled successfully.');
        }

        $newSlot = Carbon::parse($validated['scheduled_for']);
        
        $params = [
            'doctor_id' => $appointment->doctor_id,
            'scheduled_for' => $newSlot->format('Y-m-d H:i:s'),
            'exclude_id' => $appointment->id,
            'count' => null
        ];
        
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_reads.check_slot_availability(:doctor_id, TO_TIMESTAMP(:scheduled_for, 'YYYY-MM-DD HH24:MI:SS'), :exclude_id, :count); END;", $params);
        
        if ($params['count'] > 0) {
            return back()->withErrors([
                'scheduled_for' => 'The requested slot is already booked.',
            ]);
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.reschedule_appointment(:id, TO_TIMESTAMP(:scheduled_for, 'YYYY-MM-DD HH24:MI:SS')); END;", [
            'id' => $appointment->id,
            'scheduled_for' => $newSlot->format('Y-m-d H:i:s')
        ]);

        return back()->with('status', 'Appointment rescheduled successfully.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'patient', 403);

        $validated = $request->validate([
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'height_cm' => ['nullable', 'numeric', 'min:30', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'known_conditions' => ['nullable', 'string', 'max:4000'],
            'allergies' => ['nullable', 'string', 'max:4000'],
            'medical_notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $params = [
            'user_id' => $request->user()->id,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'height_cm' => $validated['height_cm'] ?? null,
            'weight_kg' => $validated['weight_kg'] ?? null,
            'known_conditions' => $validated['known_conditions'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'medical_notes' => $validated['medical_notes'] ?? null
        ];

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_patient_profile(:user_id, TO_DATE(:date_of_birth, 'YYYY-MM-DD'), :gender, :height_cm, :weight_kg, :known_conditions, :allergies, :medical_notes); END;", $params);

        return back()->with('status', 'Patient profile updated successfully.');
    }
}
