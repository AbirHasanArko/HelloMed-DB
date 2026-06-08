@extends('layouts.app')

@section('content')
<section class="section">
    <div class="meta-row" style="margin-bottom: 24px;">
        <a class="ghost-button" href="{{ route('admin.dashboard') }}">← Back to dashboard</a>
        <a class="button" href="{{ route('admin.facility_rooms.create') }}">Add facility room</a>
    </div>

    <h1>Manage Facility Rooms</h1>
    <p>View and manage all Hospital Labs, Operation Theatres, and Wards.</p>

    @if (session('status'))
        <div class="card" style="border-left:4px solid var(--primary); margin-bottom:20px;">
            <p style="margin:0; color:var(--primary); font-weight:600;">{{ session('status') }}</p>
        </div>
    @endif

    <div class="card" style="padding:0; overflow:hidden;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th style="width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        <td><strong>{{ $room->room_number }}</strong></td>
                        <td>{{ $room->room_type }}</td>
                        <td>{{ $room->capacity }} Person(s)</td>
                        <td>
                            @if ($room->is_active)
                                <span style="color:var(--success-text); background:var(--success-bg); padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600;">Active</span>
                            @else
                                <span style="color:var(--error-text); background:var(--error-bg); padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600;">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a class="ghost-button" href="{{ route('admin.facility_rooms.edit', $room) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:32px; color:var(--muted);">No facility rooms found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($rooms->hasPages())
            <div style="padding:16px; border-top:1px solid var(--border);">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
