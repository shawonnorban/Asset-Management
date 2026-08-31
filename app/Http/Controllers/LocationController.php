<?php

namespace App\Http\Controllers;

use App\Models\AssetLocation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\AuditTrailService;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = AssetLocation::orderBy('location_name')->paginate(20);
        return Inertia::render('locations/index', [
            'title' => 'Locations',
            'description' => 'Keep the physical destinations of your assets organized.',
            'rows' => $locations->getCollection()->map(fn ($location) => [
                'id' => $location->id,
                'name' => $location->location_name,
            ])->values(),
            'pagination' => $locations->toArray(),
            'canManage' => auth()->user()?->hasPermission('assets.manage') || auth()->user()?->hasPermission('locations.create') || auth()->user()?->hasPermission('locations.edit') || auth()->user()?->hasPermission('locations.update') || auth()->user()?->hasPermission('locations.delete'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('locations/form', [
            'title' => 'Add location', 'base' => '/locations', 'field' => 'location_name',
            'fieldLabel' => 'Location name', 'record' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AuditTrailService $auditTrailService)
    {
        $validated = $request->validate([
            'location_name' => 'required|string|max:50|unique:asset_locations,location_name',
        ], [
            'location_name.required' => 'The location name is required.',
            'location_name.max' => 'The location name may not be longer than 50 characters.',
            'location_name.unique' => 'That location name already exists.',
        ]);

        $location = AssetLocation::create($validated);
        $auditTrailService->created('asset_locations', $location->id, $location->toArray(), 'Created location: ' . $location->location_name);

        return redirect()->route('locations.index')
                         ->with('success', 'Location created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssetLocation $location)
    {
        return Inertia::render('locations/form', [
            'title' => 'Edit location', 'base' => '/locations', 'field' => 'location_name',
            'fieldLabel' => 'Location name', 'record' => $location,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetLocation $location, AuditTrailService $auditTrailService)
    {
        $before = $location->toArray();
        $validated = $request->validate([
            'location_name' => 'required|string|max:50|unique:asset_locations,location_name,' . $location->id,
        ], [
            'location_name.required' => 'The location name is required.',
            'location_name.max' => 'The location name may not be longer than 50 characters.',
            'location_name.unique' => 'That location name already exists.',
        ]);

        $location->update($validated);
        $auditTrailService->updated('asset_locations', $location->id, $before, $location->fresh()->toArray(), 'Updated location: ' . $location->location_name);

        return redirect()->route('locations.index')
                         ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetLocation $location, AuditTrailService $auditTrailService)
    {
        try {
            $before = $location->toArray();
            $location->delete();
            $auditTrailService->deleted('asset_locations', $before['id'], $before, 'Deleted location: ' . ($before['location_name'] ?? $before['id']));
            return redirect()->route('locations.index')
                             ->with('success', 'Location deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('locations.index')
                             ->with('error', 'Failed to delete the location. Make sure no asset is linked to it.');
        }
    }
}
