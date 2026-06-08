@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Appointments</h1>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Mode</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->patient_name }}</td>
                            <td>{{ $appointment->doctor?->name }}</td>
                            <td>{{ $appointment->service_mode }}</td>
                            <td>{{ $appointment->payment_status }}</td>
                            <td>{{ $appointment->status }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.appointments.update', $appointment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status">
                                        @foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status)
                                            <option value="{{ $status }}" @selected($appointment->status === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="button" type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 20px;">{{ $appointments->links() }}</div>
        </div>
    </section>
@endsection
