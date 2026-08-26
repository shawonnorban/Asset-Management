<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
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
        return view('departments.edit', compact('department'));
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
