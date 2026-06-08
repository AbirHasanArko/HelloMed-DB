<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('name')->paginate(20);
        return view('staff.inventory.index', compact('items'));
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

    public function edit(InventoryItem $item)
    {
        return view('staff.inventory.edit', compact('item'));
    }

    public function update(Request $request, InventoryItem $item)
    {
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
