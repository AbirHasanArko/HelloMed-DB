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

        $stmtCounts = $pdo->prepare("
            BEGIN
                SELECT COUNT(*) INTO :doctorCount FROM doctors;
                SELECT COUNT(*) INTO :departmentCount FROM departments;
                SELECT COUNT(*) INTO :appointmentCount FROM appointments;
                SELECT COUNT(*) INTO :articleCount FROM articles;
                SELECT COUNT(*) INTO :paymentCount FROM payments;
            END;
        ");

        $doctorCount = 0;
        $departmentCount = 0;
        $appointmentCount = 0;
        $articleCount = 0;
        $paymentCount = 0;

        $stmtCounts->bindParam(':doctorCount', $doctorCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmtCounts->bindParam(':departmentCount', $departmentCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmtCounts->bindParam(':appointmentCount', $appointmentCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmtCounts->bindParam(':articleCount', $articleCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmtCounts->bindParam(':paymentCount', $paymentCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        $stmtCounts->execute();

        return view('admin.dashboard', [
            'doctorCount' => $doctorCount,
            'departmentCount' => $departmentCount,
            'appointmentCount' => $appointmentCount,
            'articleCount' => $articleCount,
            'paymentCount' => $paymentCount,
            'failedLoginCount' => $failedLoginCount,
            'failedPaymentCallbackCount' => $failedPaymentCallbackCount,
            'frequentAppointmentStatusChanges' => $frequentAppointmentStatusChanges,
        ]);
    }
}
