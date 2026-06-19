@extends('layouts.app')

@section('content')
<section class="section" style="max-width: 600px;">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Book a Facility Room</h1>
            <p>Schedule a time slot for a Lab or Operation Theatre.</p>
        </div>
        <a class="ghost-button" href="{{ route('doctor.dashboard') }}">← Back</a>
    </div>

    @if(session('success'))
        <div class="card" style="border-left: 4px solid var(--success-text); margin-bottom: 20px;">
            <p style="margin:0; color:var(--success-text); font-weight:600;">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="card" style="border-left: 4px solid var(--error-text); margin-bottom: 20px;">
            <ul style="margin:0; color:var(--error-text); padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('doctor.facilities.store') }}" method="POST" class="card">
        @csrf

        <label>
            Facility Room
            <select name="facility_room_id" required>
                <option value="" disabled selected>Select a room...</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" @selected(old('facility_room_id') == $room->id)>
                        {{ $room->room_number }} ({{ $room->room_type }} - Cap: {{ $room->capacity }})
                    </option>
                @endforeach
            </select>
            @error('facility_room_id')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            Start Time
            <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required>
            @error('start_time')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            End Time
            <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required>
            @error('end_time')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <div style="margin-top: 24px;">
            <button type="submit" class="button">Book Room</button>
        </div>
    </form>

    <h2 style="margin-top: 40px; margin-bottom: 16px;">Today's Bookings</h2>
    <div class="grid cols-2">
        @forelse($rooms as $room)
        <div class="card">
            <h3 style="margin-top:0; margin-bottom:8px;">{{ $room->room_number }}</h3>
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
                                <form method="POST" action="{{ route('doctor.facilities.bookings.update', $booking->id) }}" style="margin:0;display:flex;gap:4px;">
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
            <p class="muted">No facility rooms available.</p>
        @endforelse
    </div>
</section>
@endsection
