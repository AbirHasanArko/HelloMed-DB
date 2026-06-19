<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\FacilityRoom;
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

        return view('doctor.facilities.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_room_id' => 'required|exists:facility_rooms,id',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_facilities.book_facility(:facility_room_id, :appointment_id, :user_id, :doctor_id, :start_time, :end_time, :booking_id); END;');
        
        $facilityRoomId = $validated['facility_room_id'];
        $appointmentId = $validated['appointment_id'] ?? null;
        $userId = null; 
        $doctorId = $request->user()->doctor->id;
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

        return redirect()->route('doctor.facilities.index')->with('success', 'Facility booked successfully.');
    }

    public function updateBooking(Request $request, $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,completed,cancelled'
        ]);

        $bindings = [
            'p_booking_id' => $booking,
            'p_status' => $validated['status'],
        ];

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_facilities.update_booking_status(:p_booking_id, :p_status); END;", $bindings);

        return back()->with('success', 'Booking status updated successfully.');
    }
}
