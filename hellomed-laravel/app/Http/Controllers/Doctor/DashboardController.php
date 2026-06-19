<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $request->user()->doctorProfile;
        abort_unless($doctor, 403, 'No doctor profile is linked to this account.');

        $filter = $request->string('appointment_filter')->toString();
        if (! in_array($filter, ['today', 'next', 'past', 'all'], true)) {
            $filter = 'next';
        }

        $perPage = 15;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_filters.filter_doctor_appointments(:doctor_id, :filter, :limit, :offset, :total, :cursor); END;');
        
        $doctorId = $doctor->id;
        $stmt->bindParam(':doctor_id', $doctorId, \PDO::PARAM_INT);
        $stmt->bindParam(':filter', $filter);
        $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindParam(':total', $totalCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
        $cursor = null;
        $stmt->bindParam(':cursor', $cursor, \PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($cursor);
        
        $results = [];
        while ($row = oci_fetch_assoc($cursor)) {
            $lowerRow = [];
            foreach ($row as $k => $v) {
                $lowerRow[strtolower($k)] = $v;
            }
            $results[] = $lowerRow;
        }

        $hydrated = Appointment::hydrate($results);
        foreach ($hydrated as $appt) {
            $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $appt->user_id], \App\Models\User::class)->first();
            if ($user) {
                $patientProfile = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_patient_profile(:user_id, :cursor); END;", ['user_id' => $user->id], \App\Models\PatientProfile::class)->first();
                $user->setRelation('patientProfile', $patientProfile);
            }
            $appt->setRelation('user', $user);
        }

        $appointments = new \Illuminate\Pagination\LengthAwarePaginator(
            $hydrated,
            $totalCount,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $calendarSummaryRaw = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_calendar_summary(:doctor_id, :cursor); END;", ['doctor_id' => $doctor->id]);
        
        $calendarSummary = $calendarSummaryRaw->map(function($row) {
            $obj = new \stdClass();
            $obj->appointment_date = $row->appointment_date;
            $obj->total = $row->total;
            return $obj;
        });

        return view('doctor.dashboard', [
            'doctor' => $doctor,
            'appointmentFilter' => $filter,
            'calendarSummary' => $calendarSummary,
            'appointments' => $appointments,
        ]);
    }

    public function updateSchedule(Request $request): RedirectResponse
    {
        $doctor = $request->user()->doctorProfile;
        abort_unless($doctor, 403, 'No doctor profile is linked to this account.');

        $validated = $request->validate([
            'clinic_address' => ['nullable', 'string', 'max:255'],
            'online_available_days' => ['nullable', 'array'],
            'online_available_days.*' => ['in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],
            'online_available_from' => ['nullable', 'date_format:H:i'],
            'online_available_to' => ['nullable', 'date_format:H:i', 'after:online_available_from'],
            'offline_available_days' => ['nullable', 'array'],
            'offline_available_days.*' => ['in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],
            'offline_available_from' => ['nullable', 'date_format:H:i'],
            'offline_available_to' => ['nullable', 'date_format:H:i', 'after:offline_available_from'],
            'slot_minutes' => ['required', 'integer', 'in:15,20,30,45,60'],
        ]);
        
        $onlineDays = implode(',', $request->input('online_available_days', []));
        $offlineDays = implode(',', $request->input('offline_available_days', []));
        
        // Legacy compatibility logic
        $legacyDaysArray = $request->input('offline_available_days', $request->input('online_available_days', []));
        $legacyDays = implode(',', $legacyDaysArray);
        $legacyFrom = $validated['offline_available_from'] ?? ($validated['online_available_from'] ?? null);
        $legacyTo = $validated['offline_available_to'] ?? ($validated['online_available_to'] ?? null);

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_doctor_schedule(:doctor_id, :clinic_address, :slot_minutes, :online_available, :offline_available, :online_available_days, :online_available_from, :online_available_to, :offline_available_days, :offline_available_from, :offline_available_to, :available_days, :available_from, :available_to); END;", [
            'doctor_id' => $doctor->id,
            'clinic_address' => $validated['clinic_address'] ?? null,
            'slot_minutes' => $validated['slot_minutes'],
            'online_available' => $request->boolean('online_available') ? 1 : 0,
            'offline_available' => $request->boolean('offline_available') ? 1 : 0,
            'online_available_days' => $onlineDays ?: null,
            'online_available_from' => $validated['online_available_from'] ?? null,
            'online_available_to' => $validated['online_available_to'] ?? null,
            'offline_available_days' => $offlineDays ?: null,
            'offline_available_from' => $validated['offline_available_from'] ?? null,
            'offline_available_to' => $validated['offline_available_to'] ?? null,
            'available_days' => $legacyDays ?: null,
            'available_from' => $legacyFrom,
            'available_to' => $legacyTo
        ]);

        return back()->with('status', 'Schedule updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_user_password(:id, :password); END;", [
            'id' => $request->user()->id,
            'password' => Hash::make($validated['new_password'])
        ]);

        AuditLogger::log('auth.password_changed', clone $request->user(), [], [
            'role' => $request->user()->role,
            'changed_via' => 'doctor_dashboard',
        ]);

        return back()->with('status', 'Password updated successfully.');
    }
}
