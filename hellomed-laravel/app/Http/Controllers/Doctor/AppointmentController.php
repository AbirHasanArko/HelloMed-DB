<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function show($id): View
    {
        $doctor = request()->user()->doctorProfile;
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Appointment::class)->firstOrFail();
        abort_unless($doctor && (int) $appointment->doctor_id === (int) $doctor->id, 403);

        $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $appointment->user_id], \App\Models\User::class)->first();
        if ($user) {
            $patientProfile = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_patient_profile(:user_id, :cursor); END;", ['user_id' => $user->id], \App\Models\PatientProfile::class)->first();
            $user->setRelation('patientProfile', $patientProfile);
        }
        $appointment->setRelation('user', $user);

        $chatMessages = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_chat_messages(:id, :cursor); END;", ['id' => $id], \App\Models\AppointmentChatMessage::class);
        foreach ($chatMessages as $msg) {
            $msgUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $msg->user_id], \App\Models\User::class)->first();
            $msg->setRelation('user', $msgUser);
        }
        $appointment->setRelation('chatMessages', $chatMessages);

        $prescriptionItems = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appt_prescription_items(:id, :cursor); END;", ['id' => $id], \App\Models\AppointmentPrescriptionItem::class);
        foreach ($prescriptionItems as $item) {
            $medicine = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicine_by_id(:id, :cursor); END;", ['id' => $item->medicine_id], \App\Models\Medicine::class)->first();
            $item->setRelation('medicine', $medicine);
        }
        $appointment->setRelation('prescriptionItems', $prescriptionItems);

        $medicinesCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_all_active_medicines(:cursor); END;", [], \App\Models\Medicine::class);

        return view('doctor.appointment-show', [
            'appointment' => $appointment,
            'medicines' => $medicinesCollection,
            'medicinesForJs' => $medicinesCollection
                ->map(fn ($medicine) => [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'power' => $medicine->power,
                    'amount' => $medicine->amount,
                ])
                ->values()
                ->all(),
            'existingPrescriptionItemsForJs' => old('prescription_items', $appointment->prescriptionItems->map(fn ($item) => [
                'medicine_id' => $item->medicine_id,
                'medicine_name' => $item->medicine_name,
                'amount' => $item->amount,
                'dosage' => $item->dosage,
                'intake_time' => $item->intake_time,
                'instructions' => $item->instructions,
            ])->values()->all()),
        ]);
    }

    public function updateMeetingLink(Request $request, $id): RedirectResponse
    {
        $doctor = $request->user()->doctorProfile;
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Appointment::class)->firstOrFail();
        abort_unless($doctor && (int) $appointment->doctor_id === (int) $doctor->id, 403);

        if ($appointment->service_mode !== 'online') {
            return back()->withErrors([
                'online_meeting_link' => 'Meeting link can only be set for online appointments.',
            ]);
        }

        $validated = $request->validate([
            'online_meeting_link' => ['required', 'url', 'max:1000'],
        ]);

        $oldMeetingLink = $appointment->online_meeting_link;

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_appointments.attach_meeting_link(:id, :link); END;", [
            'id' => $appointment->id,
            'link' => $validated['online_meeting_link']
        ]);
        
        $appointment->online_meeting_link = $validated['online_meeting_link'];

        AuditLogger::log('appointment.meeting_link_updated', $appointment, [
            'online_meeting_link' => $oldMeetingLink,
        ], [
            'online_meeting_link' => $appointment->online_meeting_link,
        ]);

        return back()->with('status', 'Online meeting link updated.');
    }

    public function updatePrescription(Request $request, $id): RedirectResponse
    {
        $doctor = $request->user()->doctorProfile;
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Appointment::class)->firstOrFail();
        abort_unless($doctor && (int) $appointment->doctor_id === (int) $doctor->id, 403);

        $validated = $request->validate([
            'prescription_diagnosis' => ['required', 'string', 'max:2000'],
            'prescription_medicines' => ['nullable', 'string', 'max:4000'],
            'prescription_advice' => ['required', 'string', 'max:3000'],
            'prescription_follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
            'prescription_items' => ['nullable', 'array'],
            'prescription_items.*.medicine_id' => ['nullable', 'integer', 'exists:medicines,id'],
            'prescription_items.*.medicine_name' => ['required', 'string', 'max:255'],
            'prescription_items.*.amount' => ['nullable', 'string', 'max:120'],
            'prescription_items.*.dosage' => ['nullable', 'string', 'max:120'],
            'prescription_items.*.intake_time' => ['nullable', 'string', 'max:120'],
            'prescription_items.*.instructions' => ['nullable', 'string', 'max:255'],
        ]);

        $items = collect($validated['prescription_items'] ?? [])
            ->filter(fn (array $item): bool => filled($item['medicine_name'] ?? null))
            ->values();

        $safetyWarnings = [];

        $names = $items->map(fn (array $item): string => mb_strtolower(trim((string) $item['medicine_name'])))->filter();
        if ($names->count() !== $names->unique()->count()) {
            $safetyWarnings[] = 'Duplicate medicine found in prescription list.';
        }

        $highDosageFound = $items->contains(function (array $item): bool {
            $dosage = (string) ($item['dosage'] ?? '');
            if (! preg_match('/^(\d+)(\+\d+){2,}$/', str_replace(' ', '', $dosage))) {
                return false;
            }

            $parts = array_map('intval', explode('+', str_replace(' ', '', $dosage)));
            return array_sum($parts) > 6;
        });

        if ($highDosageFound) {
            $safetyWarnings[] = 'High frequency dosage pattern detected. Please re-check dosage instructions.';
        }
        
        $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $appointment->user_id], \App\Models\User::class)->first();
        $patientProfile = null;
        if ($user) {
            $patientProfile = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_patient_profile(:user_id, :cursor); END;", ['user_id' => $user->id], \App\Models\PatientProfile::class)->first();
        }

        $allergies = collect(explode(',', (string) ($patientProfile?->allergies ?? '')))
            ->map(fn (string $allergy): string => mb_strtolower(trim($allergy)))
            ->filter();

        if ($allergies->isNotEmpty()) {
            foreach ($items as $item) {
                $medicineName = mb_strtolower((string) ($item['medicine_name'] ?? ''));
                foreach ($allergies as $allergy) {
                    if ($allergy !== '' && str_contains($medicineName, $allergy)) {
                        $safetyWarnings[] = "Possible allergy conflict: {$item['medicine_name']} may conflict with recorded allergy '{$allergy}'.";
                    }
                }
            }
        }

        $safetyWarnings = collect($safetyWarnings)->unique()->values();
        $safetyNotes = $safetyWarnings->implode("\n");

        $structuredLines = $items->map(function (array $item): string {
            $line = $item['medicine_name'];
            if (filled($item['amount'] ?? null)) {
                $line .= ' | Amount: '.$item['amount'];
            }
            if (filled($item['dosage'] ?? null)) {
                $line .= ' | Dosage: '.$item['dosage'];
            }
            if (filled($item['intake_time'] ?? null)) {
                $line .= ' | Time: '.$item['intake_time'];
            }
            if (filled($item['instructions'] ?? null)) {
                $line .= ' | Note: '.$item['instructions'];
            }

            return $line;
        })->implode("\n");

        $medicinesText = trim((string) ($validated['prescription_medicines'] ?? ''));
        if ($structuredLines !== '') {
            $medicinesText = trim($structuredLines."\n".($medicinesText !== '' ? "\nAdditional notes:\n{$medicinesText}" : ''));
        }

        $composed = "Diagnosis:\n{$validated['prescription_diagnosis']}\n\n".
            "Medicines:\n{$medicinesText}\n\n".
            "Advice:\n{$validated['prescription_advice']}";

        if (! empty($validated['prescription_follow_up_date'])) {
            $composed .= "\n\nFollow up date: {$validated['prescription_follow_up_date']}";
        }

        $oldPrescription = $appointment->only([
            'prescription_diagnosis',
            'prescription_medicines',
            'prescription_advice',
            'prescription_follow_up_date',
            'status',
        ]);
        
        $newStatus = in_array($appointment->status, ['pending', 'confirmed'], true) ? 'completed' : $appointment->status;

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_appt_prescription(:id, :doc_prescription, :diagnosis, :medicines, :advice, :safety_notes, TO_DATE(:follow_up, 'YYYY-MM-DD'), :status); END;", [
            'id' => $appointment->id,
            'doc_prescription' => $composed,
            'diagnosis' => $validated['prescription_diagnosis'],
            'medicines' => $medicinesText,
            'advice' => $validated['prescription_advice'],
            'safety_notes' => $safetyNotes !== '' ? $safetyNotes : null,
            'follow_up' => $validated['prescription_follow_up_date'] ?? null,
            'status' => $newStatus
        ]);
        
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.delete_prescription_items(:appointment_id); END;", [
            'appointment_id' => $appointment->id
        ]);
        
        foreach ($items as $index => $item) {
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_prescription_item(:appointment_id, :medicine_id, :medicine_name, :amount, :dosage, :intake_time, :instructions, :sort_order, :id); END;", [
                'appointment_id' => $appointment->id,
                'medicine_id' => $item['medicine_id'] ?? null,
                'medicine_name' => $item['medicine_name'],
                'amount' => $item['amount'] ?? null,
                'dosage' => $item['dosage'] ?? null,
                'intake_time' => $item['intake_time'] ?? null,
                'instructions' => $item['instructions'] ?? null,
                'sort_order' => $index + 1,
                'out_id' => null
            ]);
        }
        
        $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Appointment::class)->firstOrFail();
        $prescriptionItems = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appt_prescription_items(:id, :cursor); END;", ['id' => $id], \App\Models\AppointmentPrescriptionItem::class);
        $appointment->setRelation('prescriptionItems', $prescriptionItems);

        AuditLogger::log('appointment.prescription_updated', $appointment, $oldPrescription, [
            'prescription_diagnosis' => $appointment->prescription_diagnosis,
            'prescription_medicines' => $appointment->prescription_medicines,
            'prescription_advice' => $appointment->prescription_advice,
            'prescription_follow_up_date' => optional($appointment->prescription_follow_up_date)->toDateString(),
            'status' => $appointment->status,
            'prescription_items_count' => $appointment->prescriptionItems()->count(),
        ]);

        return back()->with('status', 'Prescription saved. Patient can now download it as PDF.');
    }
}
