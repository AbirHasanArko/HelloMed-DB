@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="flex-row">
            <h1>User Management</h1>
        </div>
        <p>Manage system users, update roles, and deactivate accounts.</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.role.update', $user->id) }}" style="display: flex; gap: 8px;">
                                @csrf
                                @method('PATCH')
                                <select name="role" required>
                                    <option value="patient" {{ $user->role === 'patient' ? 'selected' : '' }}>Patient</option>
                                    <option value="doctor" {{ $user->role === 'doctor' ? 'selected' : '' }}>Doctor</option>
                                    <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="pharmacist" {{ $user->role === 'pharmacist' ? 'selected' : '' }}>Pharmacist</option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <button type="submit" class="ghost-button">Update</button>
                            </form>
                        </td>
                        <td>
                            @if ($user->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Deactivated</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->is_active)
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Are you sure you want to deactivate this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ghost-button" style="color: red;">Deactivate</button>
                                </form>
                            @else
                                <span class="muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
