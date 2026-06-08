<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPatientController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('role', 'patient')->with('patientProfile');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('admin.patients.index', [
            'patients' => $query->latest()->paginate(20),
        ]);
    }

    public function show(User $patient): View
    {
        abort_unless($patient->role === 'patient', 404);

        $patient->load(['patientProfile', 'appointments.doctor', 'appointments' => function($q) {
            $q->latest('scheduled_for')->take(10);
        }]);

        return view('admin.patients.show', [
            'patient' => $patient,
        ]);
    }
}
