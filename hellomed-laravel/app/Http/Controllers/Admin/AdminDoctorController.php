<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminDoctorController extends Controller
{
    public function create()
    {
        return view('admin.doctors.create', [
            'departments' => collect(\App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_departments(:cursor); END;", [], \App\Models\Department::class)),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDoctorPayload($request, true);

        $userId = null;
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_doctor_user(:name, :email, :password, :user_id); END;", [
            'name' => $validated['name'],
            'email' => $validated['doctor_email'],
            'password' => Hash::make($validated['initial_password']),
            'user_id' => &$userId
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('doctor-photos', 'public');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_doctor_profile(:user_id, :department_id, :name, :specialty, :qualification, :experience_years, :consultation_fee, :about, :online_available_days, :offline_available_days, :available_days, :slot_minutes, :is_active, :is_featured, :featured_order, :photo_path, :online_available, :offline_available, :doctor_id); END;", [
            'user_id' => $userId,
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
            'specialty' => $validated['specialty'],
            'qualification' => $validated['qualification'] ?? null,
            'experience_years' => $validated['experience_years'],
            'consultation_fee' => $validated['consultation_fee'],
            'about' => $validated['bio'] ?? null,
            'online_available_days' => json_encode($request->input('online_available_days', [])),
            'offline_available_days' => json_encode($request->input('offline_available_days', [])),
            'available_days' => json_encode($request->input('available_days', [])),
            'slot_minutes' => $validated['slot_minutes'],
            'is_active' => $request->boolean('is_active', true) ? 1 : 0,
            'is_featured' => $request->boolean('is_featured', false) ? 1 : 0,
            'featured_order' => $request->integer('featured_order', 0),
            'photo_path' => $photoPath,
            'online_available' => $request->boolean('online_available') ? 1 : 0,
            'offline_available' => $request->boolean('offline_available') ? 1 : 0,
            'doctor_id' => null
        ]);

        $doctorUser = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_user_by_id(:id, :cursor); END;", ['id' => $userId], \App\Models\User::class)->first();

        AuditLogger::log('user.role_assigned', $doctorUser, [], [
            'role' => 'doctor',
            'is_active' => $doctorUser->is_active,
        ]);

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor account created successfully.');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Doctor::class);

        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'total' => null
        ];

        $doctorsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_doctors(:limit, :offset, :total, :cursor); END;", $params, \App\Models\Doctor::class);
        $total = $params['total'];

        foreach ($doctorsCollection as $doctor) {
            $department = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_department_by_id(:id, :cursor); END;", ['id' => $doctor->department_id], \App\Models\Department::class)->first();
            $doctor->setRelation('department', $department);
        }

        $doctors = new \Illuminate\Pagination\LengthAwarePaginator($doctorsCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('admin.doctors.index', [
            'doctors' => $doctors,
        ]);
    }

    public function edit($id)
    {
        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_doc_id(:id, :cursor); END;", ['id' => $id], \App\Models\Doctor::class)->firstOrFail();
        $this->authorize('update', $doctor);

        return view('admin.doctors.edit', [
            'doctor' => $doctor,
            'departments' => collect(\App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_active_departments(:cursor); END;", [], \App\Models\Department::class)),
        ]);
    }

    public function update(Request $request, $id)
    {
        $doctor = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_doctor_by_doc_id(:id, :cursor); END;", ['id' => $id], \App\Models\Doctor::class)->firstOrFail();
        $this->authorize('update', $doctor);

        $validated = $this->validateDoctorPayload($request, false, $doctor);

        $photoPath = $doctor->photo_path;
        if ($request->hasFile('photo')) {
            if (filled($doctor->photo_path)) {
                Storage::disk('public')->delete($doctor->photo_path);
            }
            $photoPath = $request->file('photo')->store('doctor-photos', 'public');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_doctor_profile(:id, :department_id, :specialty, :qualification, :experience_years, :consultation_fee, :about, :online_available_days, :offline_available_days, :slot_minutes, :is_active, :is_featured, :featured_order, :photo_path); END;", [
            'id' => $doctor->id,
            'department_id' => $validated['department_id'],
            'specialty' => $validated['specialty'],
            'qualification' => $validated['qualification'] ?? null,
            'experience_years' => $validated['experience_years'],
            'consultation_fee' => $validated['consultation_fee'],
            'about' => $validated['bio'] ?? null,
            'online_available_days' => json_encode($request->input('online_available_days', [])),
            'offline_available_days' => json_encode($request->input('offline_available_days', [])),
            'slot_minutes' => $validated['slot_minutes'],
            'is_active' => $request->boolean('is_active') ? 1 : 0,
            'is_featured' => $request->boolean('is_featured', false) ? 1 : 0,
            'featured_order' => $request->integer('featured_order', 0),
            'photo_path' => $photoPath
        ]);
        
        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_doctor_schedule(:id, :clinic_address, :slot_minutes, :online_available, :offline_available, :online_available_days, :online_available_from, :online_available_to, :offline_available_days, :offline_available_from, :offline_available_to, :available_days, :available_from, :available_to); END;", [
            'id' => $doctor->id,
            'clinic_address' => $validated['clinic_address'] ?? null,
            'slot_minutes' => $validated['slot_minutes'],
            'online_available' => $request->boolean('online_available') ? 1 : 0,
            'offline_available' => $request->boolean('offline_available') ? 1 : 0,
            'online_available_days' => json_encode($request->input('online_available_days', [])),
            'online_available_from' => $validated['online_available_from'] ?? null,
            'online_available_to' => $validated['online_available_to'] ?? null,
            'offline_available_days' => json_encode($request->input('offline_available_days', [])),
            'offline_available_from' => $validated['offline_available_from'] ?? null,
            'offline_available_to' => $validated['offline_available_to'] ?? null,
            'available_days' => json_encode($request->input('available_days', [])),
            'available_from' => $validated['available_from'] ?? null,
            'available_to' => $validated['available_to'] ?? null,
        ]);

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor schedule updated.');
    }

    private function validateDoctorPayload(Request $request, bool $isCreate, ?Doctor $doctor = null): array
    {
        $rules = [
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'specialty' => ['required', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'online_fee' => ['nullable', 'numeric', 'min:0'],
            'offline_fee' => ['nullable', 'numeric', 'min:0'],
            'clinic_address' => ['nullable', 'string', 'max:255'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],
            'available_from' => ['nullable', 'date_format:H:i'],
            'available_to' => ['nullable', 'date_format:H:i', 'after:available_from'],
            'online_available_days' => ['nullable', 'array'],
            'online_available_days.*' => ['in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],
            'online_available_from' => ['nullable', 'date_format:H:i'],
            'online_available_to' => ['nullable', 'date_format:H:i', 'after:online_available_from'],
            'offline_available_days' => ['nullable', 'array'],
            'offline_available_days.*' => ['in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],
            'offline_available_from' => ['nullable', 'date_format:H:i'],
            'offline_available_to' => ['nullable', 'date_format:H:i', 'after:offline_available_from'],
            'slot_minutes' => ['required', 'integer', 'in:15,20,30,45,60'],
            'is_featured' => ['nullable', 'boolean'],
            'featured_order' => ['nullable', 'integer', 'min:0'],
        ];

        if ($isCreate) {
            $rules['doctor_email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')];
            $rules['initial_password'] = ['required', 'string', 'min:8', 'max:255'];
        }

        return $request->validate($rules);
    }
}
