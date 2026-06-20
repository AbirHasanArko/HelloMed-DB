<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Support\AuditLogger;
use App\Support\NotificationService;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Appointment::class);

        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'out_total' => null
        ];

        $appointmentsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_all_appointments(:limit, :offset, :total, :cursor); END;", $params, \App\Models\Appointment::class);
        $total = \App\Helpers\OracleHelper::$lastOutParams['out_total'];

        foreach ($appointmentsCollection as $appt) {
            $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $appt->doctor_id], \App\Models\Doctor::class)->first();
            if ($doctor) {
                $department = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_department_by_id(:department_id, :cursor); END;", ['department_id' => $doctor->department_id], \App\Models\Department::class)->first();
                $doctor->setRelation('department', $department);
            }
            $appt->setRelation('doctor', $doctor);
        }

        $appointments = new \Illuminate\Pagination\LengthAwarePaginator($appointmentsCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('admin.appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    public function update(Request $request, $id)
    {
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Appointment::class)->firstOrFail();
        $this->authorize('update', $appointment);

        $oldStatus = $appointment->status;

        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_appointments.update_status(:id, :status); END;", [
            'id' => $appointment->id,
            'status' => $validated['status']
        ]);
        
        $appointment->status = $validated['status'];
        
        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $appointment->doctor_id], \App\Models\Doctor::class)->first();
        $appointment->setRelation('doctor', $doctor);
        
        $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $appointment->user_id], \App\Models\User::class)->first();

        AuditLogger::log('appointment.status_updated', $appointment, [
            'status' => $oldStatus,
        ], [
            'status' => $appointment->status,
        ]);

        NotificationService::sendEmail(
            $appointment->patient_email,
            'HelloMed Appointment Status Updated',
            "Your appointment with {$appointment->doctor->name} is now {$appointment->status}.",
            'appointment.status.updated',
            $user,
            $appointment
        );

        return back()->with('status', 'Appointment updated.');
    }
}
