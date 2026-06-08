@extends('layouts.app')

@section('content')
    <section class="section">
        <h1>Patients</h1>
        <div class="card">
            <form method="GET" action="{{ route('admin.patients.index') }}" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." style="margin-top: 0; max-width: 300px;">
                    <button type="submit" class="button" style="padding: 10px 16px;">Search</button>
                    @if(request('search'))
                        <a href="{{ route('admin.patients.index') }}" class="ghost-button" style="padding: 10px 16px;">Clear</a>
                    @endif
                </div>
            </form>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td>
                                <strong>{{ $patient->name }}</strong>
                                @if($patient->patientProfile?->known_conditions)
                                    <span class="stock-badge low-stock" style="margin-left: 8px;">Has conditions</span>
                                @endif
                                @if($patient->patientProfile?->allergies)
                                    <span class="stock-badge out-of-stock" style="margin-left: 4px;">Has allergies</span>
                                @endif
                            </td>
                            <td>{{ $patient->email }}</td>
                            <td>{{ $patient->created_at->format('M d, Y') }}</td>
                            <td>
                                <a class="ghost-button" href="{{ route('admin.patients.show', $patient) }}">View Profile</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">No patients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top: 20px;">{{ $patients->links() }}</div>
        </div>
    </section>
@endsection
