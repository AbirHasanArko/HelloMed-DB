@extends('layouts.app')

@section('content')
<section class="section">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Financial Report</h1>
            <p>Detailed financial performance and revenue metrics.</p>
        </div>
        <form method="GET" style="display:flex; gap:12px; align-items:center;">
            <input type="date" name="start_date" value="{{ $startDate }}" style="padding:8px; border:1px solid var(--border); border-radius:4px;">
            <input type="date" name="end_date" value="{{ $endDate }}" style="padding:8px; border:1px solid var(--border); border-radius:4px;">
            <button type="submit" class="button">Filter</button>
        </form>
    </div>

    <div class="grid cols-2">
        <div class="stat card">
            <span class="muted">Total Consultation Revenue</span>
            <strong style="font-size:32px; color:var(--success-text);">${{ number_format($totalRevenue, 2) }}</strong>
        </div>

        <div class="stat card">
            <span class="muted">Total Pharmacy Sales</span>
            <strong style="font-size:32px; color:#9333ea;">${{ number_format($medicineSales, 2) }}</strong>
        </div>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <h3>Net Total Revenue</h3>
        <p style="font-size: 24px; font-weight: bold;">${{ number_format($totalRevenue + $medicineSales, 2) }}</p>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="{{ route('admin.reports.index') }}" class="ghost-button">Back to General Reports</a>
    </div>
</section>
@endsection
