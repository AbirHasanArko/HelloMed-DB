@extends('layouts.app')

@section('title', 'Facility Management (Lab & OT)')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Facility Management (Lab & OT)</h1>
        <a href="{{ route('staff.facilities.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Book Facility</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($rooms as $room)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gray-100 px-4 py-3 border-b">
                <h2 class="text-xl font-bold">{{ $room->room_number }}</h2>
                <span class="text-sm text-gray-600">{{ $room->room_type }} (Cap: {{ $room->capacity }})</span>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-gray-700 mb-2">Upcoming Bookings:</h3>
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

