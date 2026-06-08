@extends('layouts.app')

@section('title', 'Facility Management (Lab & OT)')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Facility Management (Lab & OT)</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Book a Facility</h2>
        <form action="{{ route('doctor.facilities.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-gray-700 font-bold mb-2">Room</label>
                <select name="facility_room_id" class="w-full border rounded px-3 py-2" required>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->room_number }} ({{ $room->room_type }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Start Time</label>
                <input type="datetime-local" name="start_time" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">End Time</label>
                <input type="datetime-local" name="end_time" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full">Book Room</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($rooms as $room)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gray-100 px-4 py-3 border-b">
                <h2 class="text-xl font-bold">{{ $room->room_number }}</h2>
                <span class="text-sm text-gray-600">{{ $room->room_type }}</span>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Today's Bookings:</h3>
                @if($room->bookings->count() > 0)
                    <ul class="space-y-2 text-sm">
                        @foreach($room->bookings as $booking)
                        <li class="border-b pb-2">
                            <span class="font-bold">{{ $booking->start_time->format('h:i A') }} - {{ $booking->end_time->format('h:i A') }}</span>
                            <br>
                            Status: <span class="text-blue-600">{{ ucfirst($booking->status) }}</span>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 text-sm">No bookings scheduled today.</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

