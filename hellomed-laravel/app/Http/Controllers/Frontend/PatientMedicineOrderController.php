<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MedicineOrder;
use Illuminate\Http\Request;

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

    public function edit(MedicineOrder $order)
    {
        abort_unless($order->user_id === request()->user()->id, 403);
        abort_unless($order->status === 'pending', 403, 'Only pending orders can be edited.');

        return view('patient.medicine-order-edit', compact('order'));
    }

    public function update(\Illuminate\Http\Request $request, MedicineOrder $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->status === 'pending', 403, 'Only pending orders can be edited.');

        $validated = $request->validate([
            'delivery_address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_order_details(:id, :delivery_address, :phone, :status); END;", [
            'id' => $order->id,
            'delivery_address' => $validated['delivery_address'],
            'phone' => $validated['phone'],
            'status' => null,
        ]);

        return redirect()->route('patient.medicine-orders.show', $order)->with('status', 'Order details updated successfully.');
    }
}
