<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $medicines = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_medicines(:cursor); END;", [], \App\Models\Medicine::class)
            ->where('is_active', 1)
            ->sortByDesc('created_at')
            ->values();
        $page = $request->get('page', 1);
        $perPage = 16;
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($medicines->forPage($page, $perPage), $medicines->count(), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);

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
