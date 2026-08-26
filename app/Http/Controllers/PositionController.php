<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::withCount('employees')->orderBy('name')->get();

        return view('positions.index', compact('positions'));
    }

    public function create()
    {
        return view('positions.create');
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
        return view('positions.edit', compact('position'));
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
