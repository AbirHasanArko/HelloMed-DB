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

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_ambulance.request_ambulance(:user_id, :patient_name, :patient_phone, :address, :request_id); END;');
        
        $userId = auth()->id();
        $patientName = $request->patient_name;
        $patientPhone = $request->patient_phone;
        $address = $request->address;
        $requestId = null;
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':patient_name', $patientName);
        $stmt->bindParam(':patient_phone', $patientPhone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':request_id', $requestId, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
        $stmt->execute();
        
        if ($request->latitude && $request->longitude) {
            AmbulanceRequest::query()->where('id', $requestId)->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        }

        return redirect()->route('home')->with('success', 'Ambulance requested successfully! Our team will contact you immediately.');
    }
}
