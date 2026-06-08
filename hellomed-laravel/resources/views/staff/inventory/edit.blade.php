@extends('layouts.app')

@section('title', 'Update Inventory Stock')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Update Stock: {{ $item->name }}</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <p class="mb-4">Current Quantity: <strong>{{ $item->quantity }} {{ $item->unit }}</strong></p>
        
        <form action="{{ route('staff.inventory.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Quantity Change (+/-)</label>
                <input type="number" name="quantity_change" class="w-full border rounded px-3 py-2" placeholder="e.g. 5 or -2" required>
                <p class="text-xs text-gray-500 mt-1">Use negative numbers to reduce stock, positive to add.</p>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update Stock</button>
        </form>
    </div>
</div>
@endsection

