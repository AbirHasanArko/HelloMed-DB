<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_crud_reads.get_paginated_departments(:limit, :offset, :total, :cursor); END;');
        
        $limit = 1000;
        $offset = 0;
        $total = 0;
        
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindParam(':total', $total, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        
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
        
        $departments = \App\Models\Department::hydrate($results);

        return view('departments.index', [
            'departments' => $departments,
        ]);
    }

    public function show(Department $department)
    {
        $department->load(['doctors' => fn ($query) => $query->where('is_active', true)->latest()]);

        return view('departments.show', compact('department'));
    }
}
