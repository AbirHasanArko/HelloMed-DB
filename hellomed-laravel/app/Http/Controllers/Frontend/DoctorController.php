<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $departmentSlug = $request->filled('department') ? $request->input('department') : null;
        
        $perPage = 12;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_filters.filter_doctors(:dept, :limit, :offset, :total, :cursor); END;');
        
        $stmt->bindParam(':dept', $departmentSlug);
        $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindParam(':total', $totalCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
        $cursor = null;
        $stmt->bindParam(':cursor', $cursor, \PDO::PARAM_STMT);
        $stmt->execute();
        oci_execute($cursor);
        
        $results = [];
        while ($row = oci_fetch_assoc($cursor)) {
            $lowerRow = [];
            foreach ($row as $k => $v) {
                $lowerRow[strtolower($k)] = $v;
            }
            $results[] = $lowerRow;
        }

        $hydrated = Doctor::hydrate($results);
        $hydrated->load('department');

        $doctors = new \Illuminate\Pagination\LengthAwarePaginator(
            $hydrated,
            $totalCount,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $departments = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_departments(:cursor); END;", [], \App\Models\Department::class)
            ->sortBy('name')
            ->values();

        return view('doctors.index', compact('doctors', 'departments'));
    }

    public function show(Doctor $doctor)
    {
        $doctor->load([
            'department',
            'appointments',
            'reviews.user',
        ]);

        $averageRating = round((float) $doctor->reviews()->avg('rating'), 1);

        return view('doctors.show', compact('doctor', 'averageRating'));
    }
}
