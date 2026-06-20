<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\MedicineOrderItem;
use App\Models\MedicineOrder;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'out_total' => null
        ];
        
        $ordersCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_medicine_orders(:limit, :offset, :total, :cursor); END;", $params, \App\Models\MedicineOrder::class);
        $total = \App\Helpers\OracleHelper::$lastOutParams['out_total'];
        
        foreach ($ordersCollection as $order) {
            $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $order->user_id], \App\Models\User::class)->first();
            $order->setRelation('user', $user);
            
            $items = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_order_cart_items(:order_id, :cursor); END;", ['order_id' => $order->id], \App\Models\MedicineOrderItem::class);
            foreach ($items as $item) {
                if ($item->medicine_id) {
                    $med = new \App\Models\Medicine([
                        'id' => $item->medicine_id,
                        'name' => $item->medicine_name,
                        'price' => $item->medicine_price,
                        'image_path' => $item->image_path,
                    ]);
                    $item->setRelation('medicine', $med);
                }
            }
            $order->setRelation('items', $items);
        }
        
        $orders = new \Illuminate\Pagination\LengthAwarePaginator($ordersCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('pharmacist.orders.index', compact('orders'));
    }

    public function update(Request $request, $id)
    {
        $order = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_order_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\MedicineOrder::class)->firstOrFail();

        $old = [
            'status' => $order->status,
            'payment_status' => $order->payment_status,
        ];

        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
        ]);

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_medicine_order_status(:id, :status, :payment_status); END;", [
            'id' => $id,
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status']
        ]);

        $needsRelease = filled($order->inventory_committed_at)
            && blank($order->inventory_released_at)
            && ($validated['status'] === 'cancelled' || $validated['payment_status'] === 'refunded');

        if ($needsRelease) {
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_inventory.release_inventory_for_order(:order_id); END;", ['order_id' => $id]);
        }

        $order = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_order_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\MedicineOrder::class)->firstOrFail();

        AuditLogger::log('medicine_order.updated', $order, $old, [
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'inventory_released_at' => optional($order->inventory_released_at)->toDateTimeString(),
        ]);

        return back()->with('status', 'Medicine order updated.');
    }

    public function prescription($id): StreamedResponse
    {
        $order = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_order_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\MedicineOrder::class)->firstOrFail();

        abort_unless($order->prescription_path, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($order->prescription_path), 404);

        return $disk->response(
            $order->prescription_path,
            basename($order->prescription_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.basename($order->prescription_path).'"',
            ]
        );
    }
}
