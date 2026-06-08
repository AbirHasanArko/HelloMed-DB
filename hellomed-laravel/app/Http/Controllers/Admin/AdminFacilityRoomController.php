<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacilityRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFacilityRoomController extends Controller
{
    public function index(): View
    {
        return view('admin.facility_rooms.index', [
            'rooms' => FacilityRoom::query()->latest()->paginate(20),
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

        FacilityRoom::query()->create([
            'room_number' => $validated['room_number'],
            'room_type' => $validated['room_type'],
            'capacity' => $validated['capacity'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.facility_rooms.index')->with('status', 'Facility room created successfully.');
    }

    public function edit(FacilityRoom $facilityRoom): View
    {
        return view('admin.facility_rooms.edit', [
            'room' => $facilityRoom,
        ]);
    }

    public function update(Request $request, FacilityRoom $facilityRoom): RedirectResponse
    {
        $validated = $request->validate([
            'room_number' => ['required', 'string', 'max:100', 'unique:facility_rooms,room_number,'.$facilityRoom->id],
            'room_type' => ['required', 'string', 'in:Lab,Operation Theatre,General Ward,ICU'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $facilityRoom->update([
            'room_number' => $validated['room_number'],
            'room_type' => $validated['room_type'],
            'capacity' => $validated['capacity'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.facility_rooms.index')->with('status', 'Facility room updated successfully.');
    }
}
