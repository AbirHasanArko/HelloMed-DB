<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineOrder;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MedicineCartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->session()->get('medicine_cart', []);
        $medicineIds = array_keys($cart);
        $idList = implode(',', $medicineIds);
        if (empty($idList)) {
            $medicines = collect();
        } else {
            $medicines = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicines_by_ids(:p_ids, :cursor); END;", ['p_ids' => $idList], \App\Models\Medicine::class)->keyBy('id');
        }

        $items = [];
        $total = 0;

        foreach ($cart as $medicineId => $quantity) {
            $medicine = $medicines->get((int) $medicineId);
            if (! $medicine) {
                continue;
            }

            $lineTotal = (float) $medicine->price * (int) $quantity;
            $total += $lineTotal;

            $items[] = [
                'medicine' => $medicine,
                'quantity' => (int) $quantity,
                'line_total' => $lineTotal,
            ];
        }

        return view('shop.cart', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function add(Request $request, Medicine $medicine): RedirectResponse
    {
        $qty = max(1, (int) $request->input('quantity', 1));
        $cart = $request->session()->get('medicine_cart', []);
        $existing = (int) ($cart[$medicine->id] ?? 0);
        $cart[$medicine->id] = min($medicine->stock_quantity, $existing + $qty);
        $request->session()->put('medicine_cart', $cart);

        return back()->with('status', 'Medicine added to cart.');
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $qty = (int) $request->input('quantity', 1);
        $cart = $request->session()->get('medicine_cart', []);

        if ($qty <= 0) {
            unset($cart[$medicine->id]);
        } else {
            $cart[$medicine->id] = min($medicine->stock_quantity, $qty);
        }

        $request->session()->put('medicine_cart', $cart);

        return back()->with('status', 'Cart updated.');
    }

    public function remove(Request $request, Medicine $medicine): RedirectResponse
    {
        $cart = $request->session()->get('medicine_cart', []);
        unset($cart[$medicine->id]);
        $request->session()->put('medicine_cart', $cart);

        return back()->with('status', 'Medicine removed from cart.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:30'],
            'payment_method' => ['required', 'in:cash-on-delivery,bkash,nagad'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'prescription' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $cart = $request->session()->get('medicine_cart', []);
        if ($cart === []) {
            return back()->withErrors(['cart' => 'Cart is empty.']);
        }

        $medicineIds = array_keys($cart);
        $idList = implode(',', $medicineIds);
        $medicines = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicines_by_ids(:p_ids, :cursor); END;", ['p_ids' => $idList], \App\Models\Medicine::class)->keyBy('id');

        $containsPrescriptionItems = false;
        foreach ($cart as $medicineId => $quantity) {
            $medicine = $medicines->get((int) $medicineId);
            if ($medicine && $medicine->requires_prescription) {
                $containsPrescriptionItems = true;
                break;
            }
        }

        if ($containsPrescriptionItems && ! $request->hasFile('prescription')) {
            return back()->withErrors([
                'prescription' => 'Prescription file is required for one or more medicines in your cart.',
            ])->withInput();
        }

        $prescriptionPath = null;
        if ($request->hasFile('prescription')) {
            $prescriptionPath = $request->file('prescription')->store('prescriptions', 'public');
        }

        $commitInventoryNow = $validated['payment_method'] === 'cash-on-delivery';

        $bindings = [
            'p_user_id' => $request->user()->id,
            'p_delivery_address' => $validated['delivery_address'],
            'p_phone' => $validated['phone'],
            'p_payment_method' => $validated['payment_method'],
            'p_payment_callback_token' => in_array($validated['payment_method'], ['bkash', 'nagad'], true) ? Str::random(48) : null,
            'p_payment_status' => 'pending',
            'p_notes' => $validated['notes'] ?? null,
            'p_prescription_path' => $prescriptionPath,
            'p_contains_prescription_items' => $containsPrescriptionItems ? 1 : 0,
            'p_inventory_committed_at' => $commitInventoryNow ? now()->format('Y-m-d H:i:s') : null,
            'out_p_order_id' => null,
            'out_p_order_number' => null,
        ];
        
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_pharmacy.create_order(:p_user_id, :p_delivery_address, :p_phone, :p_payment_method, :p_payment_callback_token, :p_payment_status, :p_notes, :p_prescription_path, :p_contains_prescription_items, TO_TIMESTAMP(:p_inventory_committed_at, 'YYYY-MM-DD HH24:MI:SS'), :p_order_id, :p_order_number); END;", $bindings);
        
        $orderId = $bindings['out_p_order_id'];
        $orderNumber = $bindings['out_p_order_number'];

        foreach ($cart as $medicineId => $quantity) {
            $medicine = $medicines->get((int) $medicineId);
            if (! $medicine) continue;
            
            if ($medicine->stock_quantity < (int) $quantity) {
                abort(422, "Insufficient stock for {$medicine->name}");
            }
            
            $itemBindings = [
                'p_order_id' => $orderId,
                'p_medicine_id' => $medicine->id,
                'p_quantity' => $quantity,
                'p_deduct_stock' => $commitInventoryNow ? 1 : 0,
            ];
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_pharmacy.add_order_item(:p_order_id, :p_medicine_id, :p_quantity, :p_deduct_stock); END;", $itemBindings);
        }

        // We need the order object for AuditLogger and redirects. 
        // We can just fetch it.
        $orderCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_medicine_orders(:user_id, :cursor); END;", ['user_id' => $request->user()->id], \App\Models\MedicineOrder::class);
        $order = $orderCollection->where('id', $orderId)->first();

        AuditLogger::log('medicine_order.created', $order, [], [
            'payment_method' => $order->payment_method,
            'inventory_committed' => filled($order->inventory_committed_at),
        ]);

        $request->session()->forget('medicine_cart');

        if (in_array($order->payment_method, ['bkash', 'nagad'], true)) {
            return redirect()
                ->route('shop.payments.start', ['order' => $order, 'provider' => $order->payment_method])
                ->with('status', 'Order placed. Complete your payment to confirm the order.');
        }

        return redirect()->route('patient.medicine-orders.show', $order)->with('status', 'Medicine order placed successfully.');
    }
}
