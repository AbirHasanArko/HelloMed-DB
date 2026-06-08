@extends('layouts.app')

@section('content')
<section class="section" style="max-width: 600px;">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Update inventory stock</h1>
            <p>Update quantity for {{ $item->name }} (Currently {{ $item->quantity }} {{ $item->unit }}).</p>
        </div>
        <a class="ghost-button" href="{{ route('staff.inventory.index') }}">← Back</a>
    </div>

    @if(session('success'))
        <div class="card" style="border-left: 4px solid var(--success-text); margin-bottom: 20px;">
            <p style="margin:0; color:var(--success-text); font-weight:600;">{{ session('success') }}</p>
        </div>
    @endif

    <form action="{{ route('staff.inventory.update', $item->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <label>
            Adjustment quantity
            <span style="display:block; font-size:13px; color:var(--muted); margin-top:-4px; margin-bottom:4px;">Use positive numbers to add stock, negative numbers to remove stock.</span>
            <input type="number" name="quantity_change" value="0" required>
            @error('quantity_change')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <div style="margin-top: 24px;">
            <button type="submit" class="button">Update stock</button>
        </div>
    </form>
</section>
@endsection
