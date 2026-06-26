<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $perPage = 16;
        $offset = ($page - 1) * $perPage;
        
        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('BEGIN pkg_search.search_medicines(:search_val, :limit_val, :offset_val, :total, :cursor); END;');
        
        $stmt->bindParam(':search_val', $search);
        
        $stmt->bindParam(':limit_val', $perPage, \PDO::PARAM_INT);
        $stmt->bindParam(':offset_val', $offset, \PDO::PARAM_INT);
        $totalCount = 0;
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
        
        $hydrated = \App\Models\Medicine::hydrate($results);
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $hydrated,
            $totalCount,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('medicines.index', [
            'medicines' => $paginator,
        ]);
    }

    public function show(Medicine $medicine)
    {
        abort_unless($medicine->is_active, 404);

        return view('medicines.show', compact('medicine'));
    }
}
