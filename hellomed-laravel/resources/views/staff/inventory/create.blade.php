@extends('layouts.app')

@section('content')
<section class="section" style="max-width: 600px;">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Add inventory item</h1>
            <p>Enter details for a new hospital inventory item.</p>
        </div>
        <a class="ghost-button" href="{{ route('staff.inventory.index') }}">← Back</a>
    </div>

    <form action="{{ route('staff.inventory.store') }}" method="POST" class="card">
        @csrf

        <label>
            Item name
            <input type="text" name="name" value="{{ old('name') }}" required>
            @error('name')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            Category
            <select name="category" required>
                <option value="" disabled selected>Select category...</option>
                <option value="PPE" @selected(old('category') == 'PPE')>PPE</option>
                <option value="Medical Supplies" @selected(old('category') == 'Medical Supplies')>Medical Supplies</option>
                <option value="Surgical Equipment" @selected(old('category') == 'Surgical Equipment')>Surgical Equipment</option>
                <option value="General" @selected(old('category') == 'General')>General</option>
            </select>
            @error('category')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            Initial quantity
            <input type="number" name="quantity" min="0" value="{{ old('quantity', 0) }}" required>
            @error('quantity')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            Unit (e.g. boxes, pieces)
            <input type="text" name="unit" value="{{ old('unit') }}" required>
            @error('unit')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            Location
            <input type="text" name="location" value="{{ old('location') }}" required>
            @error('location')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            Status
            <select name="status" required>
                <option value="available" @selected(old('status') == 'available')>Available</option>
                <option value="low_stock" @selected(old('status') == 'low_stock')>Low Stock</option>
                <option value="out_of_stock" @selected(old('status') == 'out_of_stock')>Out of Stock</option>
            </select>
            @error('status')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <div style="margin-top: 24px;">
            <button type="submit" class="button">Save inventory item</button>
        </div>
    </form>
</section>
@endsection
