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

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare("BEGIN pkg_crud_reads.get_admin_reports(TO_TIMESTAMP(:start_date, 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP(:end_date, 'YYYY-MM-DD HH24:MI:SS'), :total_appointments, :total_revenue, :medicine_sales); END;");

        $startStr = $startDate . ' 00:00:00';
        $endStr = $endDate . ' 23:59:59';

        $stmt->bindParam(':start_date', $startStr);
        $stmt->bindParam(':end_date', $endStr);
        
        $totalAppointments = 0;
        $totalRevenue = 0;
        $medicineSales = 0;

        $stmt->bindParam(':total_appointments', $totalAppointments, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':total_revenue', $totalRevenue, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':medicine_sales', $medicineSales, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        $stmt->execute();

        return view('admin.reports.index', compact('totalAppointments', 'totalRevenue', 'medicineSales', 'startDate', 'endDate'));
    }

    public function financial(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare("BEGIN pkg_filters.get_financial_report(TO_TIMESTAMP(:start_date, 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP(:end_date, 'YYYY-MM-DD HH24:MI:SS'), :total_revenue, :medicine_sales); END;");

        $startStr = $startDate . ' 00:00:00';
        $endStr = $endDate . ' 23:59:59';

        $stmt->bindParam(':start_date', $startStr);
        $stmt->bindParam(':end_date', $endStr);
        
        $totalRevenue = 0;
        $medicineSales = 0;

        $stmt->bindParam(':total_revenue', $totalRevenue, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':medicine_sales', $medicineSales, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        $stmt->execute();

        return view('admin.reports.financial', compact('totalRevenue', 'medicineSales', 'startDate', 'endDate'));
    }
}
