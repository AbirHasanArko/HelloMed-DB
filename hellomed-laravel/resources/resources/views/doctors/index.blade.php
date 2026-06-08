@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Doctors</h1>
        <p>Find doctors by department and service mode, then request an appointment online.</p>
        <div class="grid cols-3">
            @foreach ($doctors as $doctor)
                <a class="card" href="{{ route('doctors.show', $doctor) }}">
                    <div class="tag">{{ $doctor->department?->name }}</div>
                    <h3>{{ $doctor->name }}</h3>
                    <p>{{ $doctor->specialty }}</p>
                    <div class="meta-row">
                        <span class="pill">{{ $doctor->online_available ? 'Online' : 'Offline only' }}</span>
                        <span class="pill">{{ $doctor->offline_available ? 'Offline' : 'Online only' }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div style="margin-top: 20px;">{{ $doctors->links() }}</div>
    </section>
@endsection
