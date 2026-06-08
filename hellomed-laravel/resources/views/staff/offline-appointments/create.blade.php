@extends('layouts.app')
@section('title', 'Book Offline Appointment')

@section('content')
<section class="section">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
        <h1>➕ Book Offline Appointment</h1>
        <a class="ghost-button" href="{{ route('staff.dashboard') }}">Back to Dashboard</a>
    </div>

    @if ($errors->any())
        <div class="error-box">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card fade-in" style="max-width: 800px;">
        <form method="POST" action="{{ route('staff.offline-appointments.store') }}">
            @csrf
            
            <h2 style="font-size: 1.5rem; margin-bottom: 16px;">1. Patient Details</h2>
            <p class="muted">If the email doesn't exist, a new account will be automatically created.</p>
            <div class="grid cols-2" style="margin-bottom: 24px;">
                <div>
                    <label>Patient Full Name</label>
                    <input type="text" name="patient_name" value="{{ old('patient_name') }}" required>
                </div>
                <div>
                    <label>Patient Email</label>
                    <input type="email" name="patient_email" value="{{ old('patient_email') }}" required>
                </div>
                <div>
                    <label>Patient Phone Number</label>
                    <input type="tel" name="patient_phone" value="{{ old('patient_phone') }}" required>
                </div>
            </div>

            <h3 style="font-size: 1.2rem; margin-top: 16px; margin-bottom: 12px;">Medical Profile (Optional)</h3>
            <div class="grid cols-2" style="margin-bottom: 24px;">
                <div>
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
                </div>
                <div>
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">Select Gender...</option>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                    </select>
                </div>
                <div>
                    <label>Height (cm)</label>
                    <input type="number" step="0.01" name="height_cm" value="{{ old('height_cm') }}" placeholder="e.g. 175">
                </div>
                <div>
                    <label>Weight (kg)</label>
                    <input type="number" step="0.01" name="weight_kg" value="{{ old('weight_kg') }}" placeholder="e.g. 70.5">
                </div>
                <div style="grid-column: span 2;">
                    <label>Known Conditions (comma separated)</label>
                    <input type="text" name="known_conditions" value="{{ old('known_conditions') }}" placeholder="diabetes, hypertension">
                </div>
                <div style="grid-column: span 2;">
                    <label>Known Allergies (comma separated)</label>
                    <input type="text" name="allergies" value="{{ old('allergies') }}" placeholder="penicillin, ibuprofen">
                </div>
                <div style="grid-column: span 2;">
                    <label>Medical Notes</label>
                    <textarea name="medical_notes" rows="3">{{ old('medical_notes') }}</textarea>
                </div>
            </div>

            <hr style="border:0; border-top:1px solid var(--border); margin: 32px 0;">

            <h2 style="font-size: 1.5rem; margin-bottom: 16px;">2. Appointment Details</h2>
            <div class="grid cols-2" style="margin-bottom: 24px;">
                <div style="grid-column: span 2;">
                    <label>Select Doctor</label>
                    <select name="doctor_id" required>
                        <option value="" disabled selected>-- Choose a Doctor --</option>
                        @foreach($departments as $department)
                            <optgroup label="{{ $department->name }}">
                                @foreach($department->doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                                        {{ $doctor->name }} ({{ $doctor->qualifications }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Date</label>
                    <input type="date" name="scheduled_date" min="{{ date('Y-m-d') }}" value="{{ old('scheduled_date') }}" required>
                </div>
                <div>
                    <label>Time</label>
                    <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}" required>
                </div>
                <div style="grid-column: span 2;">
                    <label>Reason for Visit</label>
                    <textarea name="reason" rows="3" required>{{ old('reason') }}</textarea>
                </div>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="button">Confirm Appointment</button>
            </div>
        </form>
    </div>
</section>
@endsection
