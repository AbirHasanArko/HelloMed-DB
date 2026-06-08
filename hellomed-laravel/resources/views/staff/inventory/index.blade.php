@extends('layouts.app')

@section('content')
<section class="section">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Inventory management</h1>
            <p>Manage hospital inventory, stock levels, and item status.</p>
        </div>
        <a class="button" href="{{ route('staff.inventory.create') }}">Add inventory item</a>
    </div>

    @if(session('success'))
        <div class="card" style="border-left: 4px solid var(--success-text); margin-bottom: 20px;">
            <p style="margin:0; color:var(--success-text); font-weight:600;">{{ session('success') }}</p>
        </div>
    @endif

    <div class="card" style="padding:0; overflow:hidden;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th style="width:120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                    <td>
                        @if($item->status === 'available')
                            <span class="muted" style="color:var(--success-text);">Available</span>
                        @elseif($item->status === 'low_stock')
                            <span class="muted" style="color:#d97706;">Low stock</span>
                        @else
                            <span class="muted" style="color:var(--error-text);">Out of stock</span>
                        @endif
                    </td>
                    <td>
                        <a class="ghost-button" href="{{ route('staff.inventory.edit', $item->id) }}">Update stock</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="muted" style="text-align:center; padding: 32px;">No inventory items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($items->hasPages())
            <div style="padding: 16px; border-top: 1px solid var(--border);">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
