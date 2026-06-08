@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Doctor schedules</h1>
        <p>Manage doctor availability windows, working days, and service channels.</p>
        <div class="meta-row" style="margin-bottom: 16px;">
            <a class="button" href="{{ route('admin.doctors.create') }}">Add new doctor</a>
        </div>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Availability</th>
                        <th>Slot</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($doctors as $doctor)
                        <tr>
                            <td>{{ $doctor->name }}</td>
                            <td>{{ $doctor->department?->name }}</td>
                            <td>{{ $doctor->available_from }} - {{ $doctor->available_to }}</td>
                            <td>{{ $doctor->slot_minutes }} min</td>
                            <td><a class="ghost-button" href="{{ route('admin.doctors.edit', $doctor) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 20px;">{{ $doctors->links() }}</div>
        </div>
    </section>
@endsection
