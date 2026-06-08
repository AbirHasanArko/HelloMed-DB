@extends('layouts.app')

@section('title', 'Book Facility')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Book Lab / OT</h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('staff.facilities.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Select Facility Room</label>
                <select name="facility_room_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- Select Room --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->room_number }} ({{ $room->room_type }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Start Time</label>
                    <input type="datetime-local" name="start_time" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">End Time</label>
                    <input type="datetime-local" name="end_time" class="w-full border rounded px-3 py-2" required>
                </div>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Book Facility</button>
        </form>
    </div>
</div>
@endsection

