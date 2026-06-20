<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminDepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $params = [
            'limit' => $perPage,
            'offset' => $offset,
            'out_total' => null
        ];

        $departmentsCollection = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_paginated_departments(:limit, :offset, :total, :cursor); END;", $params, \App\Models\Department::class);
        $total = \App\Helpers\OracleHelper::$lastOutParams['out_total'];

        $departments = new \Illuminate\Pagination\LengthAwarePaginator($departmentsCollection, $total, $perPage, $page, ['path' => $request->url()]);

        return view('admin.departments.index', [
            'departments' => $departments,
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'service_scope' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'featured_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('department-images', 'public');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.create_department(:name, :description, :service_scope, :is_active, :is_featured, :featured_order, :image_path, :id); END;", [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'service_scope' => $validated['service_scope'],
            'is_active' => $request->boolean('is_active', true) ? 1 : 0,
            'is_featured' => $request->boolean('is_featured', false) ? 1 : 0,
            'featured_order' => $request->integer('featured_order', 0),
            'image_path' => $imagePath,
            'id' => null
        ]);

        return redirect()->route('admin.departments.index')->with('status', 'Department created successfully.');
    }

    public function edit($id): View
    {
        $department = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_department_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Department::class)->firstOrFail();

        return view('admin.departments.edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $department = \App\Helpers\OracleHelper::fetchCursor("BEGIN pkg_crud_reads.get_department_by_id(:id, :cursor); END;", ['id' => $id], \App\Models\Department::class)->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,'.$department->id],
            'description' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'service_scope' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'featured_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $imagePath = $department->image_path;
        if ($request->hasFile('image')) {
            if (filled($department->image_path)) {
                Storage::disk('public')->delete($department->image_path);
            }
            $imagePath = $request->file('image')->store('department-images', 'public');
        }

        \App\Helpers\OracleHelper::executeProcedure("BEGIN pkg_crud_writes.update_department(:id, :name, :description, :service_scope, :is_active, :is_featured, :featured_order, :image_path); END;", [
            'id' => $department->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'service_scope' => $validated['service_scope'],
            'is_active' => $request->boolean('is_active') ? 1 : 0,
            'is_featured' => $request->boolean('is_featured', false) ? 1 : 0,
            'featured_order' => $request->integer('featured_order', 0),
            'image_path' => $imagePath
        ]);

        return redirect()->route('admin.departments.index')->with('status', 'Department updated successfully.');
    }
}
