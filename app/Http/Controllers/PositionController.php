<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::withCount('employees')->orderBy('name')->get();

        return Inertia::render('positions/index', [
            'title' => 'Positions',
            'description' => 'Maintain the roles available to your employees.',
            'rows' => $positions->map(fn ($position) => [
                'id' => $position->id, 'name' => $position->name,
                'usage_count' => $position->employees_count,
            ])->values(),
            'canManage' => auth()->user()?->hasPermission('positions.manage') || auth()->user()?->hasPermission('employees.manage') || auth()->user()?->hasPermission('positions.create') || auth()->user()?->hasPermission('positions.edit') || auth()->user()?->hasPermission('positions.update') || auth()->user()?->hasPermission('positions.delete') || false,
        ]);
    }

    public function create()
    {
        return Inertia::render('positions/form', [
            'title' => 'Add position', 'base' => '/positions', 'field' => 'name',
            'fieldLabel' => 'Position name', 'record' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        Position::create($validated);

        return redirect()->route('positions.index')
                         ->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        return Inertia::render('positions/form', [
            'title' => 'Edit position', 'base' => '/positions', 'field' => 'name',
            'fieldLabel' => 'Position name', 'record' => $position,
        ]);
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate($this->rules($position->id), $this->messages());

        $position->update($validated);

        return redirect()->route('positions.index')
                         ->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        try {
            $position->delete();

            return redirect()->route('positions.index')
                             ->with('success', 'Position deleted successfully.');
        } catch (\Throwable $e) {
            // an employee still holds it
            return redirect()->route('positions.index')
                             ->with('error', 'Failed to delete the position. Make sure no employee holds it.');
        }
    }

    private function rules(?int $ignoreId = null): array
    {
        $unique = 'unique:positions,name' . ($ignoreId ? ',' . $ignoreId : '');

        return ['name' => 'required|string|max:80|' . $unique];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'The position name is required.',
            'name.max'      => 'The position name may not be longer than 80 characters.',
            'name.unique'   => 'That position already exists.',
        ];
    }
}
