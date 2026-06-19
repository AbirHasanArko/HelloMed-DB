<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MedicineOrder;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MedicinePaymentController extends Controller
{
    public function start($id, string $provider)
    {
        $order = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_order_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\MedicineOrder::class)->firstOrFail();

        abort_unless($order->user_id === request()->user()->id, 403);
        abort_unless(in_array($provider, ['bkash', 'nagad'], true), 404);

        if (blank($order->payment_callback_token)) {
            $token = Str::random(48);
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_order_payment_token(:id, :token); END;", ['id' => $order->id, 'token' => $token]);
            $order->payment_callback_token = $token;
        }

        return view('shop.payments.mock-gateway', [
            'order' => $order,
            'provider' => $provider,
        ]);
    }

    public function callback(Request $request, $id, string $provider, string $status): RedirectResponse
    {
        $order = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_order_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\MedicineOrder::class)->firstOrFail();

        abort_unless($order->user_id === request()->user()->id, 403);
        abort_unless(in_array($provider, ['bkash', 'nagad'], true), 404);
        abort_unless(in_array($status, ['success', 'failed'], true), 404);
        abort_unless($request->string('token')->toString() === (string) $order->payment_callback_token, 403);

        $old = [
            'payment_status' => $order->payment_status,
            'status' => $order->status,
        ];

        if ($status === 'success') {
            if (blank($order->inventory_committed_at)) {
                \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_inventory.commit_inventory_for_order(:order_id); END;", ['order_id' => $order->id]);
            }

            $newStatus = $order->status === 'pending' ? 'processing' : $order->status;
            $paymentReference = $order->payment_reference ?? strtoupper($order->payment_method).'-'.now()->format('YmdHis').'-'.$order->id;

            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_medicine_order_status(:id, :status, :payment_status); END;", [
                'id' => $order->id,
                'status' => $newStatus,
                'payment_status' => 'paid'
            ]);

            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_order_payment(:id, :method, :status, :ref); END;", [
                'id' => $order->id,
                'method' => $order->payment_method,
                'status' => 'paid',
                'ref' => $paymentReference
            ]);

            $order = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_order_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\MedicineOrder::class)->firstOrFail();

            AuditLogger::log('medicine_order.payment_callback', $order, $old, [
                'payment_status' => $order->payment_status,
                'status' => $order->status,
            ], [
                'provider' => $provider,
                'callback_status' => 'success',
            ]);

            return redirect()->route('patient.medicine-orders.show', $order->id)->with('status', strtoupper($provider).' payment marked as paid.');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_medicine_order_status(:id, :status, :payment_status); END;", [
            'id' => $order->id,
            'status' => $order->status,
            'payment_status' => 'failed'
        ]);
        
        $order->payment_status = 'failed';

        AuditLogger::log('medicine_order.payment_callback', $order, $old, [
            'payment_status' => $order->payment_status,
            'status' => $order->status,
        ], [
            'provider' => $provider,
            'callback_status' => 'failed',
        ]);

        return redirect()->route('patient.medicine-orders.show', $order->id)->with('status', strtoupper($provider).' payment marked as failed.');
    }
}
