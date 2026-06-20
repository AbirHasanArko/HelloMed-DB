@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="grid cols-2 fade-in">
            <div class="auth-sidebar">
                <div class="auth-pattern"></div>
                <div style="position:relative;z-index:1;">
                    <div class="tag">Book appointment</div>
                    <h1 style="font-size:1.8rem;">{{ $doctor->name }}</h1>
                    <p style="font-size:15px;">{{ $doctor->department?->name }} · {{ $doctor->specialty }}</p>
                    <p>Choose online or offline care and send an appointment request.</p>
                    @php
                        $onlineDays = is_string($doctor->online_available_days) ? json_decode($doctor->online_available_days, true) : $doctor->online_available_days;
                        $offlineDays = is_string($doctor->offline_available_days) ? json_decode($doctor->offline_available_days, true) : $doctor->offline_available_days;
                    @endphp
                    
                    @if($onlineDays || $offlineDays)
                    <div style="margin-top: 24px; padding: 16px; background: rgba(255,255,255,0.1); border-radius: 8px; border: 1px solid rgba(255,255,255,0.2);">
                        <h3 style="margin-bottom:12px;font-size:16px;display:flex;align-items:center;gap:8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Doctor's Availability
                        </h3>
                        
                        <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
                            @if($onlineDays && count($onlineDays) > 0)
                            <div>
                                <strong style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;opacity:0.8;">Online Days</strong>
                                <p style="margin-top:4px;font-size:14px;">
                                    {{ implode(', ', array_map('ucfirst', $onlineDays)) }}
                                    @if($doctor->online_available_from && $doctor->online_available_to)
                                        <br><span style="font-size:13px;opacity:0.8;">Time: {{ \Carbon\Carbon::parse($doctor->online_available_from)->format('g:i A') }} - {{ \Carbon\Carbon::parse($doctor->online_available_to)->format('g:i A') }}</span>
                                    @endif
                                </p>
                            </div>
                            @endif
                            @if($offlineDays && count($offlineDays) > 0)
                            <div>
                                <strong style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;opacity:0.8;">Offline Days</strong>
                                <p style="margin-top:4px;font-size:14px;">
                                    {{ implode(', ', array_map('ucfirst', $offlineDays)) }}
                                    @if($doctor->offline_available_from && $doctor->offline_available_to)
                                        <br><span style="font-size:13px;opacity:0.8;">Time: {{ \Carbon\Carbon::parse($doctor->offline_available_from)->format('g:i A') }} - {{ \Carbon\Carbon::parse($doctor->offline_available_to)->format('g:i A') }}</span>
                                    @endif
                                </p>
                            </div>
                            @endif
                            
                            @if($doctor->slot_minutes)
                            <div>
                                <strong style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;opacity:0.8;">Time Slot</strong>
                                <p style="margin-top:4px;font-size:14px;">{{ $doctor->slot_minutes }} minutes per patient</p>
                            </div>
                            @endif
                        </div>
                        
                        @if($bookedTimes->count() > 0)
                        <strong style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;opacity:0.8;">Already Booked Times (Next 7 Days)</strong>
                        <div style="margin-top:8px;display:flex;flex-direction:column;gap:6px;">
                            @foreach($bookedTimes as $time)
                                <span style="background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.5);border-radius:6px;padding:4px 8px;font-size:13px;display:flex;align-items:center;gap:6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fca5a5" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    {{ \Carbon\Carbon::parse($time->scheduled_for)->format('M d, h:i A') }} ({{ ucfirst($time->service_mode) }})
                                </span>
                            @endforeach
                        </div>
                        <p style="margin-top:12px;font-size:13px;color:#fca5a5;">Please avoid selecting the times listed above.</p>
                        @endif
                    </div>
                    @endif
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1" opacity="0.3" style="margin-top:24px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
            </div>
            <div class="card">
                <h2 style="margin-bottom:24px;">Appointment details</h2>
                


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
                            <option value="online" @selected(old('service_mode') === 'online')>Online</option>
                            <option value="offline" @selected(old('service_mode') === 'offline')>Offline</option>
                        </select>
                    </label>
                    <label>
                        Preferred time
                        <input type="datetime-local" name="scheduled_for" value="{{ old('scheduled_for') }}" required>
                    </label>
                    <label>
                        Optional payment method
                        <select name="payment_method">
                            <option value="none" @selected(old('payment_method', 'none') === 'none')>Pay later at hospital</option>
                            <option value="bkash" @selected(old('payment_method') === 'bkash')>bKash</option>
                            <option value="nagad" @selected(old('payment_method') === 'nagad')>Nagad</option>
                            <option value="cash-counter" @selected(old('payment_method') === 'cash-counter')>Hospital cash counter</option>
                        </select>
                    </label>
                    <label>
                        Reason for visit
                        <textarea name="reason" required>{{ old('reason') }}</textarea>
                    </label>
                    <label>
                        Additional notes
                        <textarea name="notes">{{ old('notes') }}</textarea>
                    </label>
                    <button class="button" type="submit" style="width:100%;justify-content:center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
                        Submit request
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
