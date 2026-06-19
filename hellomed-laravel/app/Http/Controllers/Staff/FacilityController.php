<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\FacilityRoom;
use App\Models\FacilityBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FacilityController extends Controller
{
    public function index()
    {
        $rooms = collect(\App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_all_facility_rooms(:cursor); END;", [], \App\Models\FacilityRoom::class));
        
        foreach ($rooms as $room) {
            $bookings = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_future_facility_bookings(:room_id, :cursor); END;", ['room_id' => $room->id], \App\Models\FacilityBooking::class);
            $room->setRelation('bookings', collect($bookings));
        }

        return view('staff.facilities.index', compact('rooms'));
    }

    public function create()
    {
        $rooms = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_all_active_facility_rooms(:cursor); END;", [], \App\Models\FacilityRoom::class);
        return view('staff.facilities.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_room_id' => 'required|exists:facility_rooms,id',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_facilities.book_facility(:facility_room_id, :appointment_id, :user_id, :doctor_id, :start_time, :end_time, :booking_id); END;');
        
        $facilityRoomId = $validated['facility_room_id'];
        $appointmentId = null;
        $userId = null;
        $doctorId = null;
        $startTime = Carbon::parse($validated['start_time'])->format('Y-m-d H:i:s');
        $endTime = Carbon::parse($validated['end_time'])->format('Y-m-d H:i:s');
        $bookingId = null;

        $stmt->bindParam(':facility_room_id', $facilityRoomId);
        $stmt->bindParam(':appointment_id', $appointmentId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':doctor_id', $doctorId);
        $stmt->bindParam(':start_time', $startTime);
        $stmt->bindParam(':end_time', $endTime);
        $stmt->bindParam(':booking_id', $bookingId, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        try {
            $stmt->execute();
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Time conflict')) {
                throw ValidationException::withMessages([
                    'start_time' => 'Time conflict detected for the selected room.',
                ]);
            }
            throw $e;
        }

        return redirect()->route('staff.facilities.index')->with('success', 'Facility booked successfully.');
    }
}
