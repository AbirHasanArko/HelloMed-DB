@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="card">
            <div class="tag">Department</div>
            <h1>{{ $department->name }}</h1>
            <p>{{ $department->description }}</p>
        </div>
    </section>

    <section class="section">
        <h2>Doctors in this department</h2>
        <div class="grid cols-3">
            @forelse ($department->doctors as $doctor)
                <a class="card" href="{{ route('doctors.show', $doctor) }}">
                    <h3>{{ $doctor->name }}</h3>
                    <p>{{ $doctor->specialty }}</p>
                    <div class="muted">{{ $doctor->online_available ? 'Online' : '' }} {{ $doctor->offline_available ? 'Offline' : '' }}</div>
                </a>
            @empty
                <div class="card">No active doctors have been assigned to this department yet.</div>
            @endforelse
        </div>
    </section>
@endsection
