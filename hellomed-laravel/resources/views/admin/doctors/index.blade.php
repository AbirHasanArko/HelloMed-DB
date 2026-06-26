@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Doctor schedules</h1>
        <p>Manage doctor availability windows, working days, and service channels.</p>
        <div class="meta-row" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <a class="button" href="{{ route('admin.doctors.create') }}">Add new doctor</a>
            <form method="GET" action="{{ route('admin.doctors.index') }}" style="display: flex; gap: 8px;">
                <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <select name="department_id" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="button">Filter</button>
                @if(request('search') || request('department_id'))
                    <a href="{{ route('admin.doctors.index') }}" class="button" style="background-color: #eee; color: #333; border: 1px solid #ccc;">Clear</a>
                @endif
            </form>
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
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a class="ghost-button" href="{{ route('admin.doctors.edit', $doctor) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}" onsubmit="return confirm('Are you sure you want to delete this doctor?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ghost-button" style="color: red;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 20px;">{{ $doctors->links() }}</div>
        </div>
    </section>
@endsection
