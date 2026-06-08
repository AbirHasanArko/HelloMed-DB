@extends('layouts.app')

@section('title', 'Smart Queue Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Smart Queue Dashboard</h1>
        <form method="GET" class="flex gap-4">
            <input type="date" name="date" value="{{ $date }}" class="border rounded px-3 py-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">View</button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Token</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($appointments as $apt)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-bold">{{ $apt->token_number ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $apt->scheduled_for->format('h:i A') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $apt->patient_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $apt->doctor->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @if($apt->queue_status == 'waiting') bg-yellow-100 text-yellow-800 
                            @elseif($apt->queue_status == 'in_progress') bg-blue-100 text-blue-800
                            @elseif($apt->queue_status == 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($apt->queue_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <form action="{{ route('staff.queue.update', $apt->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <select name="queue_status" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm">
                                <option value="waiting" {{ $apt->queue_status == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                <option value="in_progress" {{ $apt->queue_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $apt->queue_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $apt->queue_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

