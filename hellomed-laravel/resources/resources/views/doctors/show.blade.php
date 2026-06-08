@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="grid cols-2">
            <div class="card">
                <div class="tag">{{ $doctor->department?->name }}</div>
                <h1>{{ $doctor->name }}</h1>
                <p>{{ $doctor->specialty }}</p>
                <p>{{ $doctor->bio }}</p>
                <div class="meta-row">
                    <span class="pill">{{ $doctor->qualification }}</span>
                    <span class="pill">{{ $doctor->experience_years }} years experience</span>
                </div>
            </div>
            <div class="card">
                <h3>Service details</h3>
                <p><strong>Online:</strong> {{ $doctor->online_available ? 'Available' : 'Not available' }}</p>
                <p><strong>Offline:</strong> {{ $doctor->offline_available ? 'Available' : 'Not available' }}</p>
                <p><strong>Clinic:</strong> {{ $doctor->clinic_address ?: 'Hospital schedule on request' }}</p>
                <p><strong>Fee:</strong> BDT {{ number_format((float) $doctor->consultation_fee, 2) }}</p>
                <a class="button" href="{{ route('appointments.create', $doctor) }}">Request appointment</a>
            </div>
        </div>
    </section>
@endsection
