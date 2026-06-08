@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>My appointments</h1>
        <p>Track your appointment status, service mode, and optional payment state.</p>
        <div class="meta-row" style="margin-bottom: 12px;">
            <a class="ghost-button" href="{{ route('patient.records') }}">Open full health records</a>
        </div>
        <div class="card" style="margin-bottom: 16px;">
            <h3>Safety profile</h3>
            <form method="POST" action="{{ route('patient.profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="grid cols-2" style="gap: 16px;">
                    <label>
                        Date of Birth
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile?->date_of_birth?->format('Y-m-d')) }}">
                    </label>
                    <label>
                        Gender
                        <select name="gender">
                            <option value="">Select Gender...</option>
                            <option value="male" @selected(old('gender', $profile?->gender) === 'male')>Male</option>
                            <option value="female" @selected(old('gender', $profile?->gender) === 'female')>Female</option>
                            <option value="other" @selected(old('gender', $profile?->gender) === 'other')>Other</option>
                        </select>
                    </label>
                    <label>
                        Height (cm)
                        <input type="number" step="0.01" name="height_cm" value="{{ old('height_cm', $profile?->height_cm) }}" placeholder="e.g. 175">
                    </label>
                    <label>
                        Weight (kg)
                        <input type="number" step="0.01" name="weight_kg" value="{{ old('weight_kg', $profile?->weight_kg) }}" placeholder="e.g. 70.5">
                    </label>
                </div>
                <label>
                    Known conditions (comma separated)
                    <input type="text" name="known_conditions" value="{{ old('known_conditions', $profile?->known_conditions) }}" placeholder="diabetes, hypertension">
                </label>
                <label>
                    Known allergies (comma separated)
                    <input type="text" name="allergies" value="{{ old('allergies', $profile?->allergies) }}" placeholder="penicillin, ibuprofen">
                </label>
                <label>
                    Medical notes
                    <textarea name="medical_notes">{{ old('medical_notes', $profile?->medical_notes) }}</textarea>
                </label>
                <button class="button" type="submit">Save profile</button>
            </form>
        </div>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Service mode</th>
                        <th>When</th>
                        <th>Appointment</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->doctor?->name }}</td>
                            <td>{{ $appointment->department?->name }}</td>
                            <td>{{ ucfirst($appointment->service_mode) }}</td>
                            <td>{{ $appointment->scheduled_for?->format('M d, Y h:i A') }}</td>
                            <td>{{ ucfirst($appointment->status) }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($appointment->payment_status)) }}</td>
                            <td><a class="ghost-button" href="{{ route('patient.appointments.show', $appointment) }}">View</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted">No appointments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top: 20px;">{{ $appointments->links() }}</div>
        </div>
    </section>
@endsection
