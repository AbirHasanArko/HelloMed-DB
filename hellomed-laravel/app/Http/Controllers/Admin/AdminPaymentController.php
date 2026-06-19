<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'total' => null
        ];

        $paymentsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_payments(:limit, :offset, :total, :cursor); END;", $params, \App\Models\Payment::class);
        $total = $params['total'];

        foreach ($paymentsCollection as $payment) {
            $user = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $payment->user_id], \App\Models\User::class)->first();
            $payment->setRelation('user', $user);

            if ($payment->payable_type === \App\Models\Appointment::class) {
                $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $payment->payable_id], \App\Models\Appointment::class)->first();
                if ($appointment) {
                    $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_profile_by_doctor_id(:id, :cursor); END;", ['id' => $appointment->doctor_id], \App\Models\DoctorProfile::class)->first();
                    if ($doctor) {
                        $doctorUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $doctor->user_id], \App\Models\User::class)->first();
                        $doctor->setRelation('user', $doctorUser);
                    }
                    $appointment->setRelation('doctor', $doctor);
                }
                $payment->setRelation('appointment', $appointment);
            }
        }

        $payments = new \Illuminate\Pagination\LengthAwarePaginator($paymentsCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('admin.payments.index', [
            'payments' => $payments,
        ]);
    }

    public function update(Request $request, $id)
    {
        $payment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_payment_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Payment::class)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,failed,refunded'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_payment(:id, :status, TO_TIMESTAMP(:paid_at, 'YYYY-MM-DD HH24:MI:SS'), :reference, :notes); END;", [
            'id' => $payment->id,
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now()->format('Y-m-d H:i:s') : null,
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null
        ]);

        if ($payment->payable_type === \App\Models\Appointment::class) {
            $appointment = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_appointment_by_id(:id, :cursor); END;", ['id' => $payment->payable_id], \App\Models\Appointment::class)->first();
            
            if ($appointment) {
                \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_appt_payment_status(:id, :payment_status); END;", [
                    'id' => $appointment->id,
                    'payment_status' => $validated['status'] === 'paid' ? 'paid' : $validated['status']
                ]);

                Mail::raw(
                    "Payment status for your appointment #{$appointment->id} is now {$validated['status']}.",
                    function ($message) use ($appointment): void {
                        $message->to($appointment->patient_email)->subject('HelloMed Payment Status Updated');
                    }
                );
            }
        }

        return back()->with('status', 'Payment status updated.');
    }
}
