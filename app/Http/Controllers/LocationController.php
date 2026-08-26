<?php

namespace App\Http\Controllers;

use App\Models\AssetLocation;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = AssetLocation::orderBy('location_name')->get();
        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:50|unique:asset_locations,location_name',
        ], [
            'location_name.required' => 'The location name is required.',
            'location_name.max' => 'The location name may not be longer than 50 characters.',
            'location_name.unique' => 'That location name already exists.',
        ]);

        AssetLocation::create($validated);

        return redirect()->route('locations.index')
                         ->with('success', 'Location created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetLocation $location)
    {
        return view('locations.edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetLocation $location)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:50|unique:asset_locations,location_name,' . $location->id,
        ], [
            'location_name.required' => 'The location name is required.',
            'location_name.max' => 'The location name may not be longer than 50 characters.',
            'location_name.unique' => 'That location name already exists.',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')
                         ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetLocation $location)
    {
        try {
            $location->delete();
            return redirect()->route('locations.index')
                             ->with('success', 'Location deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('locations.index')
                             ->with('error', 'Failed to delete the location. Make sure no asset is linked to it.');
        }
    }
}
