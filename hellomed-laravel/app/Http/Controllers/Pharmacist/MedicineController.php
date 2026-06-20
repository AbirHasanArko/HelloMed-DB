<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class MedicineController extends Controller
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

        $medicinesCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_medicines(:limit, :offset, :total, :cursor); END;", $params, \App\Models\Medicine::class);
        $total = \App\Helpers\OracleHelper::$lastOutParams['out_total'];

        $medicines = new \Illuminate\Pagination\LengthAwarePaginator($medicinesCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('pharmacist.medicines.index', compact('medicines'));
    }

    public function create()
    {
        return view('pharmacist.medicines.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'medicine_group' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'power' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $params = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'medicine_group' => $validated['medicine_group'],
            'strength' => $validated['power'],
            'amount' => $validated['amount'],
            'manufacturer' => $validated['manufacturer'] ?? null,
            'price' => $validated['price'],
            'requires_prescription' => $request->boolean('requires_prescription') ? 1 : 0,
            'stock_quantity' => $validated['stock_quantity'],
            'is_active' => $request->boolean('is_active', true) ? 1 : 0,
            'image_path' => null,
            'id' => null
        ];

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_medicine(:name, :description, :medicine_group, :strength, :amount, :manufacturer, :price, :requires_prescription, :stock_quantity, :is_active, :image_path, :id); END;", $params);

        $medicine = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_by_id(:id, :cursor); END;", ['id' => $params['id']], \App\Models\Medicine::class)->first();

        AuditLogger::log('medicine.created', $medicine, [], [
            'name' => $medicine->name,
            'power' => $medicine->strength,
            'amount' => $medicine->amount,
            'price' => $medicine->price,
            'stock_quantity' => $medicine->stock_quantity,
        ]);

        return redirect()->route('pharmacist.medicines.index')->with('status', 'Medicine created.');
    }

    public function edit($id)
    {
        $medicine = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Medicine::class)->firstOrFail();
        return view('pharmacist.medicines.edit', compact('medicine'));
    }

    public function update(Request $request, $id)
    {
        $medicine = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Medicine::class)->firstOrFail();
        $old = $medicine->only(['name', 'strength', 'amount', 'price', 'stock_quantity', 'is_active', 'requires_prescription']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'medicine_group' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'power' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $params = [
            'id' => $id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'medicine_group' => $validated['medicine_group'],
            'strength' => $validated['power'],
            'amount' => $validated['amount'],
            'manufacturer' => $validated['manufacturer'] ?? null,
            'price' => $validated['price'],
            'requires_prescription' => $request->boolean('requires_prescription') ? 1 : 0,
            'stock_quantity' => $validated['stock_quantity'],
            'is_active' => $request->boolean('is_active') ? 1 : 0,
            'image_path' => $medicine->image_path
        ];

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_medicine(:id, :name, :description, :medicine_group, :strength, :amount, :manufacturer, :price, :requires_prescription, :stock_quantity, :is_active, :image_path); END;", $params);

        $medicine = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Medicine::class)->firstOrFail();

        AuditLogger::log('medicine.updated', $medicine, $old, $medicine->only(['name', 'strength', 'amount', 'price', 'stock_quantity', 'is_active', 'requires_prescription']));

        return redirect()->route('pharmacist.medicines.index')->with('status', 'Medicine updated.');
    }
}
