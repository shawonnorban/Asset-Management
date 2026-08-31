<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MaintenanceRequest;
use App\Services\AssetLifecycleService;
use App\Support\AssetLifecycleStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaintenanceRequestController extends Controller
{
    public function index()
    {
        $requests = MaintenanceRequest::with(['asset', 'requester', 'assignee'])
            ->latest('requested_at')
            ->latest('id')
            ->paginate(15);

        return Inertia::render('maintenance-requests/index', [
            'title' => 'Maintenance Requests',
            'description' => 'Track service requests, priorities, and scheduling.',
            'requests' => $requests->getCollection()->map(fn ($request) => [
                'id' => $request->id,
                'title' => $request->title,
                'asset_id' => $request->asset_id,
                'asset_code' => $request->asset?->asset_code,
                'asset_name' => $request->asset?->asset_name,
                'maintenance_type' => $request->maintenance_type,
                'priority' => $request->priority,
                'status' => $request->status,
                'status_label' => AssetLifecycleStatus::MAINTENANCE_STATUSES[$request->status] ?? $request->status,
                'scheduled_at' => optional($request->scheduled_at)->format('d M Y'),
                'requested_by' => $request->requester?->name,
                'vendor_name' => $request->vendor_name,
            ])->values(),
            'pagination' => $requests->toArray(),
            'statuses' => AssetLifecycleStatus::MAINTENANCE_STATUSES,
            'canManage' => auth()->user()?->hasPermission('maintenance.manage') ?? false,
        ]);
    }

    public function create()
    {
        return Inertia::render('maintenance-requests/form', [
            'title' => 'Create maintenance request',
            'request' => null,
            'assets' => $this->assetOptions(),
            'statuses' => AssetLifecycleStatus::MAINTENANCE_STATUSES,
        ]);
    }

    public function store(Request $request, AssetLifecycleService $lifecycle): RedirectResponse
    {
        $data = $this->validated($request);
        $maintenanceRequest = MaintenanceRequest::create([
            ...$data,
            'requested_by' => auth()->id(),
            'status' => $data['status'] ?? 'OPEN',
            'requested_at' => $data['requested_at'] ?? today(),
        ]);

        $asset = $maintenanceRequest->asset;
        if ($asset) {
            $lifecycle->createLog(
                $asset,
                'MAINTENANCE_REQUESTED',
                'Maintenance request created: ' . $maintenanceRequest->title,
                null,
                ['request_id' => $maintenanceRequest->id, 'status' => $maintenanceRequest->status],
                auth()->id(),
            );
        }

        return redirect()->route('maintenance-requests.show', $maintenanceRequest)
            ->with('success', 'Maintenance request created successfully.');
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['asset', 'requester', 'assignee']);

        return Inertia::render('maintenance-requests/show', [
            'title' => $maintenanceRequest->title,
            'maintenanceRequest' => [
                'id' => $maintenanceRequest->id,
                'title' => $maintenanceRequest->title,
                'asset' => $maintenanceRequest->asset ? [
                    'id' => $maintenanceRequest->asset->id,
                    'code' => $maintenanceRequest->asset->asset_code,
                    'name' => $maintenanceRequest->asset->asset_name,
                ] : null,
                'maintenance_type' => $maintenanceRequest->maintenance_type,
                'priority' => $maintenanceRequest->priority,
                'description' => $maintenanceRequest->description,
                'vendor_name' => $maintenanceRequest->vendor_name,
                'status' => $maintenanceRequest->status,
                'status_label' => AssetLifecycleStatus::MAINTENANCE_STATUSES[$maintenanceRequest->status] ?? $maintenanceRequest->status,
                'scheduled_at' => optional($maintenanceRequest->scheduled_at)->format('Y-m-d'),
                'requested_at' => optional($maintenanceRequest->requested_at)->format('Y-m-d'),
                'requested_by' => $maintenanceRequest->requester?->name,
                'assigned_to' => $maintenanceRequest->assignee?->name,
            ],
        ]);
    }

    private function assetOptions(): array
    {
        return Asset::orderBy('asset_code')
            ->get()
            ->map(fn ($asset) => [
                'id' => $asset->id,
                'label' => $asset->asset_code . ' - ' . $asset->asset_name,
            ])
            ->values()
            ->all();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'maintenance_type' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'max:30'],
            'scheduled_at' => ['nullable', 'date'],
            'vendor_name' => ['nullable', 'string', 'max:150'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'requested_at' => ['nullable', 'date'],
        ]);
    }
}
