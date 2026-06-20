@extends('layouts.app')

@section('content')
<section class="section">
    <div class="card">
        <div class="tag">Edit order details</div>
        <h1>{{ $order->order_number }}</h1>

        <form method="POST" action="{{ route('patient.medicine-orders.update', $order) }}">
            @csrf
            @method('PUT')
            
            <label>
                Delivery address
                <textarea name="delivery_address" required>{{ old('delivery_address', $order->delivery_address) }}</textarea>
            </label>
            <label>
                Phone
                <input type="text" name="phone" value="{{ old('phone', $order->phone) }}" required>
            </label>
            
            <div class="pill-row">
                <button type="submit" class="button">Save changes</button>
                <a href="{{ route('patient.medicine-orders.show', $order) }}" class="ghost-button">Cancel</a>
            </div>
        </form>
    </div>
</section>
@endsection
