@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Departments</h1>
        <p>Browse the hospital's care areas, from emergency and medicine to orthopedics, dentistry, and cardiology.</p>
        <div class="grid cols-2">
            @foreach ($departments as $department)
                <a class="card" href="{{ route('departments.show', $department) }}">
                    <div class="tag">{{ $department->service_scope }}</div>
                    <h3>{{ $department->name }}</h3>
                    <p>{{ $department->description }}</p>
                    <div class="muted">{{ $department->doctors_count }} doctors</div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
