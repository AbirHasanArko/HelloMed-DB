<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        
        $appointments = Appointment::with(['doctor', 'user'])
            ->whereDate('scheduled_for', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_for')
            ->get();
            
        return view('staff.queue.index', compact('appointments', 'date'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'queue_status' => 'required|in:waiting,in_progress,completed,cancelled',
        ]);

        $pdo = DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_appointments.update_queue_status(:appointment_id, :queue_status); END;');
        
        $appointmentId = $appointment->id;
        $queueStatus = $validated['queue_status'];

        $stmt->bindParam(':appointment_id', $appointmentId);
        $stmt->bindParam(':queue_status', $queueStatus);

        $stmt->execute();

        return redirect()->back()->with('success', 'Queue status updated successfully.');
    }
}
