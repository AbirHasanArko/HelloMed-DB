@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Admin dashboard</h1>
        <div class="grid cols-4">
            <div class="stat"><strong>{{ $doctorCount }}</strong><span class="muted">Doctors</span></div>
            <div class="stat"><strong>{{ $departmentCount }}</strong><span class="muted">Departments</span></div>
            <div class="stat"><strong>{{ $appointmentCount }}</strong><span class="muted">Appointments</span></div>
            <div class="stat"><strong>{{ $articleCount }}</strong><span class="muted">Articles</span></div>
        </div>
    </section>
@endsection
