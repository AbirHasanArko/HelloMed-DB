<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Appointment;
use App\Models\Article;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $sinceHours = 24;
        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_filters.get_admin_dashboard_metrics(:since, :failed_logins, :failed_payments, :freq_status); END;');
        
        $stmt->bindParam(':since', $sinceHours, \PDO::PARAM_INT);
        $stmt->bindParam(':failed_logins', $failedLoginCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':failed_payments', $failedPaymentCallbackCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':freq_status', $frequentAppointmentStatusChanges, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
        $stmt->execute();

        return view('admin.dashboard', [
            'doctorCount' => Doctor::query()->count(),
            'departmentCount' => Department::query()->count(),
            'appointmentCount' => Appointment::query()->count(),
            'articleCount' => Article::query()->count(),
            'paymentCount' => Payment::query()->count(),
            'failedLoginCount' => $failedLoginCount,
            'failedPaymentCallbackCount' => $failedPaymentCallbackCount,
            'frequentAppointmentStatusChanges' => $frequentAppointmentStatusChanges,
        ]);
    }
}
