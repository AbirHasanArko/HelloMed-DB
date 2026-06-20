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
        
        $appointments = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_queue_appointments_by_date(:date_str, :cursor); END;", ['date_str' => $date], \App\Models\Appointment::class);
            
        foreach ($appointments as $appointment) {
            $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_doc_id(:id, :cursor); END;", ['id' => $appointment->doctor_id], \App\Models\Doctor::class)->first();
            $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $appointment->user_id], \App\Models\User::class)->first();
            
            if ($doctor) {
                $userDoc = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $doctor->user_id], \App\Models\User::class)->first();
                $doctor->setRelation('user', $userDoc);
                $appointment->setRelation('doctor', $doctor);
            }
            if ($user) {
                $appointment->setRelation('user', $user);
            }
        }
            
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
