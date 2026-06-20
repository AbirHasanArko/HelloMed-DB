@extends('layouts.app')
@section('title', 'Hospital Facilities')

@section('content')
<div class="hero-section text-center" style="padding: 48px 0; background: linear-gradient(135deg, var(--bg) 0%, #e0f2fe 100%);">
    <div class="container">
        <h1>Our Facilities</h1>
        <p class="muted" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
            Explore our state-of-the-art facility rooms available for various medical needs.
        </p>
    </div>
</div>

<section class="section container">
    <div class="grid cols-3" style="margin-top: 32px;">
        @forelse($rooms as $room)
            <div class="card fade-in" style="display:flex; flex-direction:column;">
                <div class="tag" style="align-self:flex-start; margin-bottom:12px; background:var(--primary-light); color:var(--primary);">
                    {{ $room->type }}
                </div>
                <h3 style="margin:0 0 8px;">Room {{ $room->room_number }}</h3>
                <p class="muted" style="flex:1;">{{ $room->description ?: 'No description available for this room.' }}</p>
                <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <strong>${{ number_format($room->price_per_day, 2) }} / day</strong>
                </div>
            </div>
        @empty
            <div class="card text-center" style="grid-column: 1 / -1; padding: 48px;">
                <p class="muted">No facility rooms are currently active.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
