<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();

        return Inertia::render('departments/index', [
            'title' => 'Departments',
            'description' => 'Organize employees and ownership by department.',
            'rows' => $departments->map(fn ($department) => [
                'id' => $department->id, 'name' => $department->name,
                'usage_count' => $department->employees_count,
            ])->values(),
            'canManage' => auth()->user()?->hasPermission('departments.manage') || auth()->user()?->hasPermission('employees.manage') || auth()->user()?->hasPermission('departments.create') || auth()->user()?->hasPermission('departments.edit') || auth()->user()?->hasPermission('departments.update') || auth()->user()?->hasPermission('departments.delete') || false,
        ]);
    }

    public function create()
    {
        return Inertia::render('departments/form', [
            'title' => 'Add department', 'base' => '/departments', 'field' => 'name',
            'fieldLabel' => 'Department name', 'record' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        Department::create($validated);

        return redirect()->route('departments.index')
                         ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return Inertia::render('departments/form', [
            'title' => 'Edit department', 'base' => '/departments', 'field' => 'name',
            'fieldLabel' => 'Department name', 'record' => $department,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate($this->rules($department->id), $this->messages());

        $department->update($validated);

        return redirect()->route('departments.index')
                         ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        try {
            $department->delete();

            return redirect()->route('departments.index')
                             ->with('success', 'Department deleted successfully.');
        } catch (\Throwable $e) {
            // an employee still points at it
            return redirect()->route('departments.index')
                             ->with('error', 'Failed to delete the department. Make sure no employee belongs to it.');
        }
    }

    private function rules(?int $ignoreId = null): array
    {
        $unique = 'unique:departments,name' . ($ignoreId ? ',' . $ignoreId : '');

        return ['name' => 'required|string|max:80|' . $unique];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'The department name is required.',
            'name.max'      => 'The department name may not be longer than 80 characters.',
            'name.unique'   => 'That department already exists.',
        ];
    }
}
