<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminStaffController extends Controller
{
    public function create(): View
    {
        return view('admin.staff.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'initial_password' => ['required', 'string', 'min:8', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $pdo = \Illuminate\Support\Facades\DB::getPdo();
        $stmt = $pdo->prepare('
            BEGIN
                INSERT INTO users (name, email, password, role, is_active)
                VALUES (:name, :email, :password, :role, :is_active)
                RETURNING id INTO :id;
            END;
        ');

        $name = $validated['name'];
        $email = $validated['email'];
        $password = Hash::make($validated['initial_password']);
        $role = 'staff';
        $isActive = $request->boolean('is_active', true) ? 1 : 0;
        $id = null;

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':is_active', $isActive, \PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);

        $stmt->execute();
        
        $staff = new User([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'is_active' => $isActive,
        ]);
        $staff->id = $id;

        AuditLogger::log('user.role_assigned', $staff, [], [
            'role' => 'staff',
            'is_active' => $staff->is_active,
        ]);

        return redirect()->route('admin.dashboard')->with('status', 'Staff account created successfully.');
    }
}
