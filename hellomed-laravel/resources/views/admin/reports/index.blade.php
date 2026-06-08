@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics Dashboard</h1>
        <form method="GET" class="flex gap-4">
            <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-2">
            <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-600 mb-2">Total Appointments</h2>
            <p class="text-4xl font-bold text-blue-600">{{ $totalAppointments }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-600 mb-2">Consultation Revenue</h2>
            <p class="text-4xl font-bold text-green-600">${{ number_format($totalRevenue, 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-600 mb-2">Pharmacy Sales</h2>
            <p class="text-4xl font-bold text-purple-600">${{ number_format($medicineSales, 2) }}</p>
        </div>
    </div>
</div>
@endsection

