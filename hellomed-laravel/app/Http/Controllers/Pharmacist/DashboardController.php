<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare("
            BEGIN
                SELECT COUNT(*) INTO :medicineCount FROM medicines;
                SELECT COUNT(*) INTO :lowStockCount FROM medicines WHERE stock_quantity <= 10;
                SELECT COUNT(*) INTO :pendingOrders FROM medicine_orders WHERE status = 'pending';
                SELECT COUNT(*) INTO :processingOrders FROM medicine_orders WHERE status = 'processing';
            END;
        ");

        $medicineCount = 0;
        $lowStockCount = 0;
        $pendingOrders = 0;
        $processingOrders = 0;

        $stmt->bindParam(':medicineCount', $medicineCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':lowStockCount', $lowStockCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':pendingOrders', $pendingOrders, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':processingOrders', $processingOrders, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
        $stmt->execute();

        return view('pharmacist.dashboard', [
            'medicineCount' => $medicineCount,
            'lowStockCount' => $lowStockCount,
            'pendingOrders' => $pendingOrders,
            'processingOrders' => $processingOrders,
        ]);
    }
}
