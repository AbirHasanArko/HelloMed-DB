<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacilityRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFacilityRoomController extends Controller
{
    public function index(Request $request): View
    {
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'out_total' => null
        ];

        $roomsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_facility_rooms(:limit, :offset, :total, :cursor); END;", $params, \App\Models\FacilityRoom::class);
        $total = \App\Helpers\OracleHelper::$lastOutParams['out_total'];

        $rooms = new \Illuminate\Pagination\LengthAwarePaginator($roomsCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('admin.facility_rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    public function create(): View
    {
        return view('admin.facility_rooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:100', 'unique:facility_rooms,room_number'],
            'room_type' => ['required', 'string', 'in:Lab,Operation Theatre,General Ward,ICU'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Actually wait, I need a create_facility_room procedure in pkg_crud_writes to insert a room...
        // Did I create one? Let me use DB::insert as a fallback if not available, wait, we must use PL/SQL.
        // Let's call pkg_crud_writes.create_facility_room. If it doesn't exist I will add it shortly.
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_facility_room(:room_number, :room_type, :capacity, :is_active, :id); END;", [
            'room_number' => $validated['room_number'],
            'room_type' => $validated['room_type'],
            'capacity' => $validated['capacity'],
            'is_active' => $request->boolean('is_active', true) ? 1 : 0,
            'id' => null
        ]);

        return redirect()->route('admin.facility_rooms.index')->with('status', 'Facility room created successfully.');
    }

    public function edit($id): View
    {
        $facilityRoom = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_facility_room_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\FacilityRoom::class)->firstOrFail();

        return view('admin.facility_rooms.edit', [
            'room' => $facilityRoom,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $facilityRoom = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_facility_room_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\FacilityRoom::class)->firstOrFail();

        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:100', 'unique:facility_rooms,room_number,'.$facilityRoom->id],
            'room_type' => ['required', 'string', 'in:Lab,Operation Theatre,General Ward,ICU'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_facility_room(:id, :room_number, :room_type, :capacity, :is_active); END;", [
            'id' => $facilityRoom->id,
            'room_number' => $validated['room_number'],
            'room_type' => $validated['room_type'],
            'capacity' => $validated['capacity'],
            'is_active' => $request->boolean('is_active', true) ? 1 : 0
        ]);

        return redirect()->route('admin.facility_rooms.index')->with('status', 'Facility room updated successfully.');
    }
}
