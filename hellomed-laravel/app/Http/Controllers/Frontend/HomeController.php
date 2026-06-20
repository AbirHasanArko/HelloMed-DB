<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare("BEGIN pkg_crud_reads.get_homepage_data(:dept_cursor, :doc_cursor, :art_cursor, :patient_count, :dept_count, :doc_count); END;");
        
        $deptCursor = null;
        $docCursor = null;
        $artCursor = null;
        $patientCount = 0;
        $deptCount = 0;
        $docCount = 0;

        $stmt->bindParam(':dept_cursor', $deptCursor, \PDO::PARAM_STMT);
        $stmt->bindParam(':doc_cursor', $docCursor, \PDO::PARAM_STMT);
        $stmt->bindParam(':art_cursor', $artCursor, \PDO::PARAM_STMT);
        $stmt->bindParam(':patient_count', $patientCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':dept_count', $deptCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
        $stmt->bindParam(':doc_count', $docCount, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        $stmt->execute();
        
        $fetchCursor = function($cursor, $modelClass) {
            oci_execute($cursor);
            $results = [];
            while ($row = oci_fetch_assoc($cursor)) {
                $lowercaseRow = [];
                foreach ($row as $k => $v) {
                    if (is_object($v) && (get_class($v) === 'OCILob' || get_class($v) === 'OCI-Lob' || method_exists($v, 'load'))) {
                        $v = $v->load();
                    }
                    $lowercaseRow[strtolower($k)] = $v;
                }
                $results[] = $lowercaseRow;
            }
            oci_free_statement($cursor);
            return $modelClass::hydrate($results);
        };

        return view('public.home', [
            'departments' => $fetchCursor($deptCursor, \App\Models\Department::class),
            'doctors' => $fetchCursor($docCursor, \App\Models\Doctor::class),
            'articles' => $fetchCursor($artCursor, \App\Models\Article::class),
            'patientCount' => $patientCount,
            'totalDepartments' => $deptCount,
            'totalDoctors' => $docCount,
        ]);
    }

    public function about()
    {
        return view('public.about');
    }
}
