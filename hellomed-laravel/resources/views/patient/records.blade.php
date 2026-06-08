@extends('layouts.app')

@section('content')
<section class="section">
    <h1>My health records</h1>
    <p>Unified timeline of your appointments, prescriptions, and medicine orders.</p>

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

    <div class="grid cols-2">
        <div class="card">
            <h3>Appointment history</h3>
            <div class="list">
                @forelse ($appointments as $appointment)
                    <div class="list-item">
                        <strong>{{ $appointment->doctor?->name }} · {{ ucfirst($appointment->status) }}</strong>
                        <p>{{ $appointment->scheduled_for?->format('M d, Y h:i A') }} · {{ $appointment->department?->name }}</p>
                        @if ($appointment->doctor_prescription)
                            <a class="ghost-button" href="{{ route('patient.appointments.show', $appointment) }}">View prescription</a>
                        @endif
                    </div>
                @empty
                    <div class="list-item">No appointment records yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h3>Medicine order history</h3>
            <div class="list">
                @forelse ($medicineOrders as $order)
                    <div class="list-item">
                        <strong>{{ $order->order_number }} · {{ ucfirst($order->status) }}</strong>
                        <p>Payment: {{ ucfirst($order->payment_status) }} · BDT {{ number_format((float) $order->total_amount, 2) }}</p>
                        <a class="ghost-button" href="{{ route('patient.medicine-orders.show', $order) }}">View order</a>
                    </div>
                @empty
                    <div class="list-item">No medicine order records yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
