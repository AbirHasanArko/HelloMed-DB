@extends('layouts.app')

@section('content')
    <section class="hero">
        <div>
            <div class="tag">Whole hospital platform</div>
            <h1>Care across departments, doctors, and articles in one place.</h1>
            <p>Patients can browse specialties, choose online or offline services, request appointments, and read hospital articles without leaving the platform.</p>
            <div class="pill-row">
                <a class="button" href="{{ route('doctors.index') }}">Book a doctor</a>
                <a class="ghost-button" href="{{ route('articles.index') }}">Read articles</a>
            </div>
        </div>
        <div class="card">
            <h3>What this platform covers</h3>
            <div class="list">
                <div class="list-item">
                    <strong>Online appointments</strong>
                    <p>Consult doctors remotely for supported specialties.</p>
                </div>
                <div class="list-item">
                    <strong>Offline hospital visits</strong>
                    <p>Book in-person consultations for departments like orthopedics, dental, and cardiac care.</p>
                </div>
                <div class="list-item">
                    <strong>Article publishing</strong>
                    <p>Publish and organize general health and hospital articles from the backend.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="grid cols-4">
            <div class="stat"><strong>24/7</strong><span class="muted">Booking access</span></div>
            <div class="stat"><strong>{{ $departments->count() }}</strong><span class="muted">Active departments</span></div>
            <div class="stat"><strong>{{ $doctors->count() }}</strong><span class="muted">Featured doctors</span></div>
            <div class="stat"><strong>{{ $articles->count() }}</strong><span class="muted">Recent articles</span></div>
        </div>
    </section>

    <section class="section">
        <h2>Departments</h2>
        <div class="grid cols-3">
            @foreach ($departments as $department)
                <a class="card" href="{{ route('departments.show', $department) }}">
                    <div class="tag">{{ $department->service_scope }}</div>
                    <h3>{{ $department->name }}</h3>
                    <p>{{ $department->description }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="section">
        <h2>Doctors</h2>
        <div class="grid cols-4">
            @foreach ($doctors as $doctor)
                <a class="card" href="{{ route('doctors.show', $doctor) }}">
                    <div class="tag">{{ $doctor->department?->name }}</div>
                    <h3>{{ $doctor->name }}</h3>
                    <p>{{ $doctor->specialty }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="section">
        <h2>Latest articles</h2>
        <div class="grid cols-3">
            @foreach ($articles as $article)
                <a class="card" href="{{ route('articles.show', $article) }}">
                    <div class="tag">{{ $article->category?->name }}</div>
                    <h3>{{ $article->title }}</h3>
                    <p>{{ $article->excerpt }}</p>
                </a>
            @endforeach
        </div>
    </section>
@endsection
