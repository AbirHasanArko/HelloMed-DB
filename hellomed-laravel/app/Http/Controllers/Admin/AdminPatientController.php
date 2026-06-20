<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPatientController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->filled('search') ? $request->search : null;
        $perPage = 20;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;

        $params = [
            'search' => $search,
            'limit' => $perPage,
            'offset' => $offset,
            'out_total' => null
        ];

        $patientsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_search.search_patients(:search, :limit, :offset, :total, :cursor); END;", $params, \App\Models\User::class);
        $totalCount = $params['out_total'];

        foreach ($patientsCollection as $patient) {
            $patientProfile = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_patient_profile(:user_id, :cursor); END;", ['user_id' => $patient->id], \App\Models\PatientProfile::class)->first();
            $patient->setRelation('patientProfile', $patientProfile);
        }

        $patients = new \Illuminate\Pagination\LengthAwarePaginator(
            $patientsCollection,
            $totalCount,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('admin.patients.index', [
            'patients' => $patients,
        ]);
    }

    public function show($id): View
    {
        $patient = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\User::class)->firstOrFail();
        abort_unless($patient->role === 'patient', 404);

        $patientProfile = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_patient_profile(:user_id, :cursor); END;", ['user_id' => $patient->id], \App\Models\PatientProfile::class)->first();
        $patient->setRelation('patientProfile', $patientProfile);

        $appointments = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_recent_patient_appts(:user_id, :limit, :cursor); END;", ['user_id' => $patient->id, 'limit' => 10], \App\Models\Appointment::class);
        
        foreach ($appointments as $appointment) {
            $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_doc_id(:id, :cursor); END;", ['id' => $appointment->doctor_id], \App\Models\Doctor::class)->first();
            if ($doctor) {
                $doctorUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $doctor->user_id], \App\Models\User::class)->first();
                $doctor->setRelation('user', $doctorUser);
            }
            $appointment->setRelation('doctor', $doctor);
        }
        $patient->setRelation('appointments', $appointments);

        return view('admin.patients.show', [
            'patient' => $patient,
        ]);
    }
}
