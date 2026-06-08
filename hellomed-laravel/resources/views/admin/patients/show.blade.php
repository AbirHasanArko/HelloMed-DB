@extends('layouts.app')

@section('content')
    <section class="section">
        <div style="margin-bottom: 20px;">
            <a href="{{ route('admin.patients.index') }}" class="ghost-button">&larr; Back to Patients</a>
        </div>
        
        <div class="grid cols-2">
            <div class="card">
                <div class="tag">Patient Information</div>
                <h1 style="margin-bottom: 8px;">{{ $patient->name }}</h1>
                <p><strong>Email:</strong> {{ $patient->email }}</p>
                <p><strong>Registered:</strong> {{ $patient->created_at->format('M d, Y h:i A') }}</p>

                <h3 style="margin-top: 24px;">Medical Profile</h3>
                @php $profile = $patient->patientProfile; @endphp
                @if ($profile)
                    <table class="table" style="margin-top: 12px; border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
                        <tbody>
                            <tr><th style="width: 150px;">Date of Birth</th><td>{{ $profile->date_of_birth ? $profile->date_of_birth->format('F d, Y') : 'N/A' }}</td></tr>
                            <tr><th>Gender</th><td>{{ $profile->gender ? ucfirst($profile->gender) : 'N/A' }}</td></tr>
                            <tr><th>Height</th><td>{{ $profile->height_cm ? $profile->height_cm . ' cm' : 'N/A' }}</td></tr>
                            <tr><th>Weight</th><td>{{ $profile->weight_kg ? $profile->weight_kg . ' kg' : 'N/A' }}</td></tr>
                            <tr><th>Known Conditions</th><td><span style="color:#b91c1c; font-weight:600;">{{ $profile->known_conditions ?: 'None reported' }}</span></td></tr>
                            <tr><th>Allergies</th><td><span style="color:#b91c1c; font-weight:600;">{{ $profile->allergies ?: 'None reported' }}</span></td></tr>
                            <tr><th>Medical Notes</th><td style="white-space: pre-wrap;">{{ $profile->medical_notes ?: 'No additional notes' }}</td></tr>
                            <tr><th>Last Updated</th><td>{{ $profile->updated_at ? $profile->updated_at->diffForHumans() : 'Never' }}</td></tr>
                        </tbody>
                    </table>
                @else
                    <div class="notice" style="margin-top: 12px;">This patient has not filled out their medical profile yet.</div>
                @endif
            </div>
            
            <div class="card">
                <div class="tag">Recent Appointments</div>
                <h3 style="margin-top: 12px; margin-bottom: 16px;">Latest 10 Appointments</h3>
                <div class="list">
                    @forelse($patient->appointments as $appointment)
                        <div class="list-item">
                            <strong>{{ $appointment->doctor?->name }} · <span style="color:var(--primary);">{{ ucfirst($appointment->status) }}</span></strong>
                            <p style="margin-bottom: 0;">{{ $appointment->scheduled_for?->format('M d, Y h:i A') }} · {{ ucfirst($appointment->service_mode) }}</p>
                        </div>
                    @empty
                        <div class="list-item muted">No appointments found for this patient.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
