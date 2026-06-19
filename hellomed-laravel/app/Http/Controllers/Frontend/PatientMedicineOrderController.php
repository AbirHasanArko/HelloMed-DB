<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MedicineOrder;

class PatientMedicineOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_medicine_orders(:user_id, :cursor); END;", ['user_id' => $request->user()->id], \App\Models\MedicineOrder::class)->sortByDesc('created_at')->values();
        $page = $request->get('page', 1);
        $perPage = 15;
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($orders->forPage($page, $perPage), $orders->count(), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);

        return view('patient.medicine-orders', [
            'orders' => $paginator,
        ]);
    }

    public function show(MedicineOrder $order)
    {
        abort_unless($order->user_id === request()->user()->id, 403);

        return view('patient.medicine-order-show', [
            'order' => $order->load('items.medicine'),
        ]);
    }
}
