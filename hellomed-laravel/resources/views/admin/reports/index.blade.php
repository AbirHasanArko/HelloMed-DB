@extends('layouts.app')

@section('content')
<section class="section">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Reports & Analytics Dashboard</h1>
            <p>View top-level hospital metrics and performance.</p>
        </div>
        <form method="GET" style="display:flex; gap:12px; align-items:center;">
            <input type="date" name="start_date" value="{{ $startDate }}" style="padding:8px; border:1px solid var(--border); border-radius:4px;">
            <input type="date" name="end_date" value="{{ $endDate }}" style="padding:8px; border:1px solid var(--border); border-radius:4px;">
            <button type="submit" class="button">Filter</button>
        </form>
    </div>

    <div class="grid cols-3">
        <div class="stat card">
            <span class="muted">Total Appointments</span>
            <strong style="font-size:32px; color:var(--primary);">{{ $totalAppointments }}</strong>
        </div>

        <div class="stat card">
            <span class="muted">Consultation Revenue</span>
            <strong style="font-size:32px; color:var(--success-text);">${{ number_format($totalRevenue, 2) }}</strong>
        </div>

        <div class="stat card">
            <span class="muted">Pharmacy Sales</span>
            <strong style="font-size:32px; color:#9333ea;">${{ number_format($medicineSales, 2) }}</strong>
        </div>
    </div>
</section>
@endsection
