@extends('layouts.app')

@section('content')
<section class="section" style="max-width: 600px;">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Book a Facility Room</h1>
            <p>Schedule a time slot for a Lab or Operation Theatre.</p>
        </div>
        <a class="ghost-button" href="{{ route('staff.facilities.index') }}">← Back</a>
    </div>

    @if($errors->any())
        <div class="card" style="border-left: 4px solid var(--error-text); margin-bottom: 20px;">
            <ul style="margin:0; color:var(--error-text); padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('staff.facilities.store') }}" method="POST" class="card">
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
</section>
@endsection
