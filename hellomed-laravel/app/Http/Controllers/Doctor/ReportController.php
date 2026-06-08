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
        $doctor = $request->user()->doctor;
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $totalAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('scheduled_for', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->count();
            
        $completedAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->whereBetween('scheduled_for', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->count();

        return view('doctor.reports.index', compact('totalAppointments', 'completedAppointments', 'startDate', 'endDate'));
    }
}
