<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppointmentChatController extends Controller
{
    public function index(Request $request, $id): JsonResponse
    {
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Appointment::class)->firstOrFail();
        
        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $appointment->doctor_id], \App\Models\Doctor::class)->first();
        $appointment->setRelation('doctor', $doctor);

        $user = $request->user();
        $this->assertParticipant($appointment, $user->id);

        if ($appointment->status !== 'confirmed') {
            return response()->json([
                'enabled' => false,
                'messages' => [],
            ]);
        }

        $chatMessages = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_chat_messages(:id, :cursor); END;", ['id' => $id], \App\Models\AppointmentChatMessage::class);
        
        $messages = $chatMessages->map(function ($message) use ($user) {
                $msgUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $message->user_id], \App\Models\User::class)->first();
                return [
                    'id' => $message->id,
                    'sender_id' => $message->user_id,
                    'sender_name' => $msgUser?->name,
                    'is_mine' => (int) $message->user_id === $user->id,
                    'message' => $message->message,
                    'created_at' => $message->created_at?->format('M d, Y h:i A'),
                    'read_at' => $message->read_at?->format('M d, Y h:i A'),
                    'attachment_url' => $message->attachment_path ? Storage::disk('public')->url($message->attachment_path) : null,
                    'attachment_name' => $message->attachment_name,
                ];
            })
            ->values();

        return response()->json([
            'enabled' => true,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, $id): RedirectResponse
    {
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Appointment::class)->firstOrFail();
        
        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $appointment->doctor_id], \App\Models\Doctor::class)->first();
        $appointment->setRelation('doctor', $doctor);

        $user = $request->user();
        $this->assertParticipant($appointment, $user->id);

        if ($appointment->status !== 'confirmed') {
            return back()->withErrors([
                'message' => 'Chat is available only after appointment confirmation.',
            ]);
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120', 'required_without:message'],
        ]);

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('appointment-chat-attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
            $attachmentMime = $file->getMimeType();
            $attachmentSize = $file->getSize();
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_appt_chat_message(:appointment_id, :user_id, :message, :attachment_path, :attachment_name, :attachment_mime, :attachment_size, :id); END;", [
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
            'message' => $validated['message'] ?? '',
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'attachment_size' => $attachmentSize,
            'out_id' => null
        ]);

        return back()->with('status', 'Message sent.');
    }

    public function markRead(Request $request, $id): JsonResponse
    {
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Appointment::class)->firstOrFail();
        
        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_id(:id, :cursor); END;", ['id' => $appointment->doctor_id], \App\Models\Doctor::class)->first();
        $appointment->setRelation('doctor', $doctor);

        $user = $request->user();
        $this->assertParticipant($appointment, $user->id);

        if ($appointment->status !== 'confirmed') {
            return response()->json(['updated' => 0]);
        }

        $params = [
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
            'updated' => null
        ];
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.mark_appt_chat_messages_read(:appointment_id, :user_id, :updated); END;", $params);

        return response()->json(['updated' => $params['updated']]);
    }

    private function assertParticipant(Appointment $appointment, int $userId): void
    {
        $doctorUserId = $appointment->doctor?->user_id;
        $isPatient = (int) $appointment->user_id === $userId;
        $isAssignedDoctor = $doctorUserId && (int) $doctorUserId === $userId;

        abort_unless($isPatient || $isAssignedDoctor, 403);
    }
}
