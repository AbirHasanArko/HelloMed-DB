@extends('layouts.app')

@section('content')
<section class="section" style="max-width: 600px;">
    <div class="meta-row" style="margin-bottom: 24px;">
        <a class="ghost-button" href="{{ route('admin.facility_rooms.index') }}">← Back to list</a>
    </div>

    <h1>Add Facility Room</h1>
    
    <form method="POST" action="{{ route('admin.facility_rooms.store') }}" class="card">
        @csrf

        <label>
            Room Number (e.g., LAB-01, OT-A)
            <input type="text" name="room_number" value="{{ old('room_number') }}" required>
            @error('room_number')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            Room Type
            <select name="room_type" required>
                <option value="" disabled selected>Select a type...</option>
                <option value="Lab" @selected(old('room_type') === 'Lab')>Lab</option>
                <option value="Operation Theatre" @selected(old('room_type') === 'Operation Theatre')>Operation Theatre</option>
                <option value="General Ward" @selected(old('room_type') === 'General Ward')>General Ward</option>
                <option value="ICU" @selected(old('room_type') === 'ICU')>ICU</option>
            </select>
            @error('room_type')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            Capacity (People)
            <input type="number" name="capacity" value="{{ old('capacity', 1) }}" min="1" required>
            @error('capacity')<span style="color:var(--error-text);font-size:13px;">{{ $message }}</span>@enderror
        </label>

        <label>
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
            Room is active and available for booking
        </label>

        <div style="margin-top: 24px;">
            <button type="submit" class="button">Create Facility Room</button>
        </div>
    </form>
</section>
@endsection
