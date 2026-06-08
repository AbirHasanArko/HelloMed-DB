@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="grid cols-2">
            <div class="card">
                <div class="tag">Book appointment</div>
                <h1>{{ $doctor->name }}</h1>
                <p>{{ $doctor->department?->name }} · {{ $doctor->specialty }}</p>
                <p>Choose online or offline care and send an appointment request.</p>
            </div>
            <div class="card">
                <form method="POST" action="{{ route('appointments.store') }}">
                    @csrf
                    <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
                    <input type="hidden" name="department_id" value="{{ $doctor->department_id }}">

                    <label>
                        Patient name
                        <input type="text" name="patient_name" value="{{ old('patient_name') }}" required>
                    </label>
                    <label>
                        Email
                        <input type="email" name="patient_email" value="{{ old('patient_email') }}" required>
                    </label>
                    <label>
                        Phone
                        <input type="text" name="patient_phone" value="{{ old('patient_phone') }}" required>
                    </label>
                    <label>
                        Service mode
                        <select name="service_mode" required>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                        </select>
                    </label>
                    <label>
                        Preferred time
                        <input type="datetime-local" name="scheduled_for" value="{{ old('scheduled_for') }}" required>
                    </label>
                    <label>
                        Reason for visit
                        <textarea name="reason" required>{{ old('reason') }}</textarea>
                    </label>
                    <label>
                        Additional notes
                        <textarea name="notes">{{ old('notes') }}</textarea>
                    </label>
                    <button class="button" type="submit">Submit request</button>
                </form>
            </div>
        </div>
    </section>
@endsection
