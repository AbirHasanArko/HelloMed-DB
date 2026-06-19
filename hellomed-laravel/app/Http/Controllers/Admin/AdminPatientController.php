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
        $search = $request->filled('search') ? $request->search : null;
        $perPage = 20;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_search.search_patients(:search, :limit, :offset, :total, :cursor); END;');
        
        $stmt->bindParam(':search', $search);
        $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindParam(':total', $totalCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
        $cursor = null;
        $stmt->bindParam(':cursor', $cursor, \PDO::PARAM_STMT);
        
        $stmt->execute();
        
        oci_execute($cursor);
        $results = [];
        while ($row = oci_fetch_assoc($cursor)) {
            // Lowercase keys from Oracle to match Laravel expectations
            $lowerRow = [];
            foreach ($row as $k => $v) {
                $lowerRow[strtolower($k)] = $v;
            }
            $results[] = $lowerRow;
        }

        $hydrated = \App\Models\User::hydrate($results);
        $hydrated->load('patientProfile');

        $patients = new \Illuminate\Pagination\LengthAwarePaginator(
            $hydrated,
            $totalCount,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('admin.patients.index', [
            'patients' => $patients,
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
