<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\OracleHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_admin_staff_users(:cursor); END;");
        
        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,staff,pharmacist,doctor,patient'
        ]);

        $bindings = [
            'p_user_id' => $id,
            'p_role' => $request->role,
        ];

        OracleHelper::executeProcedure("BEGIN pkg_users.update_role(:p_user_id, :p_role); END;", $bindings);

        return back()->with('success', 'User role updated successfully.');
    }

    public function destroy($id)
    {
        $bindings = [
            'p_user_id' => $id,
        ];

        OracleHelper::executeProcedure("BEGIN pkg_users.deactivate_user(:p_user_id); END;", $bindings);

        return back()->with('success', 'User deactivated successfully.');
    }
}
