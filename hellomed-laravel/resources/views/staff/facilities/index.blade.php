@extends('layouts.app')

@section('content')
<section class="section">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Labs & Operation Theatres</h1>
            <p>View facility rooms and their schedule for today.</p>
        </div>
        <a class="button" href="{{ route('staff.facilities.create') }}">Book a facility</a>
    </div>

    @if(session('success'))
        <div class="card" style="border-left: 4px solid var(--success-text); margin-bottom: 20px;">
            <p style="margin:0; color:var(--success-text); font-weight:600;">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid cols-2">
        @forelse($rooms as $room)
        <div class="card">
            <h3 style="margin-top:0; margin-bottom:8px;">{{ $room->room_number }}</h3>
            <p class="muted" style="margin-bottom:16px;">{{ $room->room_type }} • Capacity: {{ $room->capacity }}</p>
            
            <h4 style="margin-bottom:8px; font-size:14px; text-transform:uppercase; color:var(--muted);">Today's Bookings</h4>
            <div style="background:var(--surface); padding:12px; border-radius:8px;">
                @forelse($room->bookings as $booking)
                    <div style="padding:8px 0; border-bottom:1px solid var(--border); display:flex; justify-content:space-between;">
                        <span style="font-weight:500; color:var(--text);">
                            {{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}
                        </span>
                        <span class="muted" style="font-size:13px; display:flex; gap: 8px; align-items:center;">
                            @if(in_array($booking->status, ['completed', 'cancelled']))
                                {{ ucfirst($booking->status) }}
                            @else
                                <form method="POST" action="{{ route('staff.facilities.bookings.update', $booking->id) }}" style="margin:0;display:flex;gap:4px;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" style="padding: 2px; font-size: 12px; height: auto;">
                                        <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $booking->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </form>
                            @endif
                        </span>
                    </div>
                @empty
                    <p class="muted" style="margin:0; font-size:14px; text-align:center;">No bookings today.</p>
                @endforelse
            </div>
        </div>
        @empty
        <div class="card" style="grid-column: span 2;">
            <p class="muted" style="text-align:center;">No facility rooms available.</p>
        </div>
        @endforelse
    </div>
</section>
@endsection
