<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_profile_by_user_id(:user_id, :cursor); END;", ['user_id' => $request->user()->id], \App\Models\DoctorProfile::class)->first();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare("BEGIN pkg_crud_reads.get_doctor_reports(:doctor_id, TO_TIMESTAMP(:start_date, 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP(:end_date, 'YYYY-MM-DD HH24:MI:SS'), :total_appointments, :completed_appointments); END;");

        $startStr = $startDate . ' 00:00:00';
        $endStr = $endDate . ' 23:59:59';
        $doctorId = $doctor->id;

        $stmt->bindParam(':doctor_id', $doctorId);
        $stmt->bindParam(':start_date', $startStr);
        $stmt->bindParam(':end_date', $endStr);
        
        $totalAppointments = 0;
        $completedAppointments = 0;

        $stmt->bindParam(':total_appointments', $totalAppointments, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':completed_appointments', $completedAppointments, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        $stmt->execute();

        return view('doctor.reports.index', compact('totalAppointments', 'completedAppointments', 'startDate', 'endDate'));
    }
}
