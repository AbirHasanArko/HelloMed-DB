@extends('layouts.app')

@section('title', 'Add Inventory Item')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Add New Inventory Item</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('staff.inventory.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Item Name</label>
                <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Category</label>
                <input type="text" name="category" class="w-full border rounded px-3 py-2">
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Initial Quantity</label>
                    <input type="number" name="quantity" class="w-full border rounded px-3 py-2" min="0" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Unit (e.g. Boxes, Pcs)</label>
                    <input type="text" name="unit" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Storage Location</label>
                <input type="text" name="location" class="w-full border rounded px-3 py-2">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Item</button>
        </form>
    </div>
</div>
@endsection

