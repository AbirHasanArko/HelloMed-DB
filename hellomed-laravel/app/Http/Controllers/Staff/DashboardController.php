<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Article;
use App\Models\Doctor;

class DashboardController extends Controller
{
    public function index()
    {
        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare("BEGIN pkg_crud_reads.get_staff_dashboard_stats(:pending_appointments, :today_appointments, :doctor_count, :published_articles, :pending_ambulance); END;");

        $pendingAppointments = 0;
        $todayAppointments = 0;
        $doctorCount = 0;
        $publishedArticles = 0;
        $pendingAmbulance = 0;

        $stmt->bindParam(':pending_appointments', $pendingAppointments, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':today_appointments', $todayAppointments, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':doctor_count', $doctorCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':published_articles', $publishedArticles, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':pending_ambulance', $pendingAmbulance, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        $stmt->execute();

        return view('staff.dashboard', compact(
            'pendingAppointments',
            'todayAppointments',
            'doctorCount',
            'publishedArticles',
            'pendingAmbulance'
        ));
    }
}
