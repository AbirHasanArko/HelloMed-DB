<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FacilityRoom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function index(Request $request): View
    {
        $rooms = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_facility_rooms(:cursor); END;", [], FacilityRoom::class);

        return view('public.facilities.index', compact('rooms'));
    }
}
