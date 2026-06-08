<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicineOrder;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $totalAppointments = Appointment::whereBetween('scheduled_for', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count();
        $totalRevenue = Payment::where('status', 'paid')->whereBetween('paid_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->sum('amount');
        $medicineSales = MedicineOrder::where('status', 'completed')->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->sum('total_amount');

        return view('admin.reports.index', compact('totalAppointments', 'totalRevenue', 'medicineSales', 'startDate', 'endDate'));
    }
}
