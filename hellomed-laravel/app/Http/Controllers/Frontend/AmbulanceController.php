<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AmbulanceRequest;
use Illuminate\Http\Request;

class AmbulanceController extends Controller
{
    public function create()
    {
        return view('ambulance.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:255',
            'address' => 'required_without:latitude|string|nullable|max:1000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $params = [
            'user_id' => auth()->id(),
            'patient_name' => $request->patient_name,
            'patient_phone' => $request->patient_phone,
            'address' => $request->address,
            'out_request_id' => null
        ];
        
        $params = \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_ambulance.request_ambulance(:user_id, :patient_name, :patient_phone, :address, :request_id); END;", $params);
        
        $requestId = $params['out_request_id'];
        
        if ($request->latitude && $request->longitude && $requestId) {
            \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_ambulance_location(:id, :latitude, :longitude); END;", [
                'id' => $requestId,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]);
        }

        return redirect()->route('home')->with('success', 'Ambulance requested successfully! Our team will contact you immediately.');
    }
}
