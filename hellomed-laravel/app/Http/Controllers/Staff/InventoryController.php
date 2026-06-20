<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'total' => null
        ];

        $itemsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_inventory_items(:limit, :offset, :total, :cursor); END;", $params, \App\Models\InventoryItem::class);
        $total = $params['total'];

        $items = new \Illuminate\Pagination\LengthAwarePaginator($itemsCollection, $total, $perPage, $page, ['path' => $request->url()]);

        $alerts = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_inventory.get_inventory_alerts(:threshold, :cursor); END;", ['threshold' => 10]);

        return view('staff.inventory.index', compact('items', 'alerts'));
    }

    public function create()
    {
        return view('staff.inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
        ]);

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_inventory.add_item(:name, :category, :quantity, :unit, :location, :item_id); END;');
        
        $name = $validated['name'];
        $category = $validated['category'] ?? null;
        $quantity = $validated['quantity'];
        $unit = $validated['unit'] ?? null;
        $location = $validated['location'] ?? null;
        $itemId = null;

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':unit', $unit);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':item_id', $itemId, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        $stmt->execute();

        return redirect()->route('staff.inventory.index')->with('success', 'Inventory item added successfully.');
    }

    public function edit($id)
    {
        $item = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_inventory_item_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\InventoryItem::class)->firstOrFail();
        return view('staff.inventory.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_inventory_item_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\InventoryItem::class)->firstOrFail();

        $validated = $request->validate([
            'quantity_change' => 'required|integer',
        ]);

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_inventory.update_stock(:item_id, :quantity_change); END;');
        
        $itemId = $item->id;
        $quantityChange = $validated['quantity_change'];

        $stmt->bindParam(':item_id', $itemId);
        $stmt->bindParam(':quantity_change', $quantityChange);

        $stmt->execute();

        return redirect()->route('staff.inventory.index')->with('success', 'Inventory stock updated successfully.');
    }
}
