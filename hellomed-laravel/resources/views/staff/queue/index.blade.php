@extends('layouts.app')

@section('content')
<section class="section">
    <div class="nav-inner" style="padding: 0 0 16px;">
        <div>
            <h1>Smart Queue System</h1>
            <p>Manage today's patient queue and appointment statuses.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="card" style="border-left: 4px solid var(--success-text); margin-bottom: 20px;">
            <p style="margin:0; color:var(--success-text); font-weight:600;">{{ session('success') }}</p>
        </div>
    @endif

    <div class="card" style="padding:0; overflow:hidden;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th>Token</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                <tr>
                    <td><strong style="font-size:18px;">{{ $appointment->token_number ?? 'N/A' }}</strong></td>
                    <td>{{ $appointment->patient->name }}</td>
                    <td>{{ $appointment->doctor->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('h:i A') }}</td>
                    <td>
                        @if($appointment->queue_status == 'waiting')
                            <span class="muted" style="color:var(--warning-text);">Waiting</span>
                        @elseif($appointment->queue_status == 'in_progress')
                            <span class="muted" style="color:var(--primary);">In Progress</span>
                        @elseif($appointment->queue_status == 'completed')
                            <span class="muted" style="color:var(--success-text);">Completed</span>
                        @else
                            <span class="muted">{{ ucfirst($appointment->queue_status) }}</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('staff.queue.update', $appointment->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <select name="queue_status" onchange="this.form.submit()" style="padding:4px 8px; border-radius:4px; border:1px solid var(--border); font-size:13px; background:var(--surface); color:var(--text);">
                                <option value="waiting" @selected($appointment->queue_status == 'waiting')>Waiting</option>
                                <option value="in_progress" @selected($appointment->queue_status == 'in_progress')>In Progress</option>
                                <option value="completed" @selected($appointment->queue_status == 'completed')>Completed</option>
                                <option value="cancelled" @selected($appointment->queue_status == 'cancelled')>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="muted" style="text-align:center; padding: 32px;">No appointments in the queue for today.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
