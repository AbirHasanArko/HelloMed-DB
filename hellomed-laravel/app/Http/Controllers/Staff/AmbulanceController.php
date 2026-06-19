<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AmbulanceRequest;
use Illuminate\Http\Request;

class AmbulanceController extends Controller
{
    public function index()
    {
        $requests = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_ambulance_requests(:cursor); END;", [], \App\Models\AmbulanceRequest::class);

        return view('staff.ambulance.index', compact('requests'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,dispatched,resolved,cancelled',
            'notes' => 'nullable|string',
        ]);

        $ambulanceRequest = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_ambulance_request_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\AmbulanceRequest::class)->firstOrFail();

        $params = [
            'id' => $ambulanceRequest->id,
            'staff_id' => $ambulanceRequest->staff_id,
            'status' => $request->status,
            'dispatched_at' => $ambulanceRequest->dispatched_at,
            'resolved_at' => $ambulanceRequest->resolved_at,
            'notes' => $request->notes,
        ];

        if ($request->status === 'dispatched' && $ambulanceRequest->status !== 'dispatched') {
            $params['dispatched_at'] = now()->format('Y-m-d H:i:s');
            $params['staff_id'] = auth()->id();
        } elseif ($request->status === 'resolved' && $ambulanceRequest->status !== 'resolved') {
            $params['resolved_at'] = now()->format('Y-m-d H:i:s');
            if (!$ambulanceRequest->staff_id) {
                $params['staff_id'] = auth()->id();
            }
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_ambulance_request(:id, :staff_id, :status, TO_TIMESTAMP(:dispatched_at, 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP(:resolved_at, 'YYYY-MM-DD HH24:MI:SS'), :notes); END;", $params);

        return back()->with('success', 'Ambulance request updated successfully.');
    }
}
