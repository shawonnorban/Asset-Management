<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetTransfer;
use App\Models\AssetLocation;
use App\Models\Employee;
use App\Services\AssetLifecycleService;
use App\Services\NotificationService;
use App\Support\AssetLifecycleStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'status' => (string) $request->query('status', ''),
            'asset_id' => (string) $request->query('asset_id', ''),
        ];

        $transfers = AssetTransfer::with(['asset', 'requester', 'approver', 'fromLocation', 'toLocation'])
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['asset_id'], fn ($query, $assetId) => $query->where('asset_id', $assetId))
            ->latest('requested_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('transfers/index', [
            'title' => 'Asset Transfers',
            'description' => 'Manage transfer requests, approvals, and movement history.',
            'transfers' => $transfers->getCollection()->map(fn ($transfer) => [
                'id' => $transfer->id,
                'asset_id' => $transfer->asset_id,
                'asset_code' => $transfer->asset?->asset_code,
                'asset_name' => $transfer->asset?->asset_name,
                'from_location' => $transfer->fromLocation?->location_name,
                'to_location' => $transfer->toLocation?->location_name,
                'status' => $transfer->status,
                'status_label' => AssetLifecycleStatus::TRANSFER_STATUSES[$transfer->status] ?? $transfer->status,
                'requested_at' => optional($transfer->requested_at)->format('d M Y'),
                'requested_by' => $transfer->requester?->name,
                'approved_by' => $transfer->approver?->name,
                'reason' => $transfer->reason,
            ])->values(),
            'pagination' => $transfers->toArray(),
            'statuses' => AssetLifecycleStatus::TRANSFER_STATUSES,
            'filters' => $filters,
            'summary' => $this->summary(),
            'canManage' => (bool) auth()->user()?->hasPermission('transfers.manage'),
        ]);
    }

    public function create()
    {
        return Inertia::render('transfers/form', [
            'title' => 'Request asset transfer',
            'transfer' => null,
            'assets' => Asset::orderBy('asset_code')->get()->map(fn ($asset) => ['id' => $asset->id, 'label' => $asset->asset_code . ' - ' . $asset->asset_name])->values()->all(),
            'locations' => AssetLocation::orderBy('location_name')->get()->map(fn ($location) => ['id' => $location->id, 'label' => $location->location_name])->values()->all(),
            'employees' => Employee::orderBy('name')->get()->map(fn ($employee) => ['id' => $employee->id, 'label' => $employee->name])->values()->all(),
            'statuses' => AssetLifecycleStatus::TRANSFER_STATUSES,
        ]);
    }

    public function store(Request $request, AssetLifecycleService $lifecycle)
    {
        $data = $this->validated($request);

        $transfer = AssetTransfer::create([
            ...$data,
            'requested_by' => auth()->id(),
            'status' => 'REQUESTED',
            'requested_at' => $data['requested_at'] ?? today(),
        ]);

        $lifecycle->createLog(
            $transfer->asset,
            'TRANSFER_REQUESTED',
            'Transfer requested for asset ' . ($transfer->asset?->asset_code ?? 'N/A'),
            null,
            ['transfer_id' => $transfer->id, 'status' => $transfer->status, 'reason' => $transfer->reason],
            auth()->id(),
        );

        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer request created.');
    }

    public function approve(Request $request, AssetTransfer $transfer, AssetLifecycleService $lifecycle, NotificationService $notifications)
    {
        $request->validate(['notes' => ['nullable', 'string']]);

        if (! in_array($transfer->status, ['REQUESTED', 'APPROVED'], true)) {
            return back()->with('error', 'This transfer cannot be approved in its current state.');
        }

        DB::transaction(function () use ($request, $transfer, $lifecycle) {
            $asset = $transfer->asset;
            $previousStatus = $transfer->status;

            $transfer->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id(),
                'notes' => $request->input('notes', $transfer->notes),
                'transferred_at' => $transfer->transferred_at ?? now()->toDateString(),
            ]);

            if ($asset) {
                $asset->update([
                    'location_id' => $transfer->to_location_id ?? $asset->location_id,
                    'employee_id' => $transfer->to_employee_id ?? $asset->employee_id,
                    'status' => $transfer->to_employee_id ? 'IN_USE' : ($asset->employee_id ? 'IN_USE' : 'IN_STORAGE'),
                ]);

                $lifecycle->createLog(
                    $asset,
                    'TRANSFER_COMPLETED',
                    'Transfer approved for asset ' . $asset->asset_code,
                    ['status' => $previousStatus],
                    [
                        'status' => 'APPROVED',
                        'location_id' => $transfer->to_location_id ?? $asset->location_id,
                        'employee_id' => $transfer->to_employee_id ?? $asset->employee_id,
                        'approved_by' => auth()->user()?->name,
                        'notes' => $transfer->notes,
                    ],
                    auth()->id(),
                );
            }
        });

        $this->notifyRequester(
            $notifications,
            $transfer,
            'transfer_approved',
            'Transfer approved',
            'Your transfer request for ' . ($transfer->asset?->asset_code ?? 'the asset') . ' has been approved.'
        );

        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer approved and asset location updated.');
    }

    public function reject(Request $request, AssetTransfer $transfer, AssetLifecycleService $lifecycle, NotificationService $notifications)
    {
        $request->validate(['notes' => ['nullable', 'string']]);

        if ($transfer->status !== 'REQUESTED') {
            return back()->with('error', 'Only a pending transfer request can be rejected.');
        }

        $transfer->update([
            'status' => 'REJECTED',
            'approved_by' => auth()->id(),
            'notes' => $request->input('notes', $transfer->notes),
        ]);

        if ($transfer->asset) {
            $lifecycle->createLog(
                $transfer->asset,
                'TRANSFER_REQUESTED',
                'Transfer rejected for asset ' . $transfer->asset->asset_code,
                ['status' => 'REQUESTED'],
                ['status' => 'REJECTED', 'approved_by' => auth()->user()?->name, 'notes' => $transfer->notes],
                auth()->id(),
            );
        }

        $this->notifyRequester(
            $notifications,
            $transfer,
            'transfer_rejected',
            'Transfer rejected',
            'Your transfer request for ' . ($transfer->asset?->asset_code ?? 'the asset') . ' has been rejected.'
        );

        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer request rejected.');
    }

    public function show(AssetTransfer $transfer)
    {
        $transfer->load(['asset', 'requester', 'approver', 'fromLocation', 'toLocation', 'fromEmployee', 'toEmployee']);

        return Inertia::render('transfers/show', [
            'title' => 'Transfer detail',
            'transfer' => [
                'id' => $transfer->id,
                'asset' => $transfer->asset ? ['id' => $transfer->asset->id, 'code' => $transfer->asset->asset_code, 'name' => $transfer->asset->asset_name] : null,
                'from_location' => $transfer->fromLocation ? ['name' => $transfer->fromLocation->location_name] : null,
                'to_location' => $transfer->toLocation ? ['name' => $transfer->toLocation->location_name] : null,
                'from_employee' => $transfer->fromEmployee ? ['name' => $transfer->fromEmployee->name] : null,
                'to_employee' => $transfer->toEmployee ? ['name' => $transfer->toEmployee->name] : null,
                'status' => $transfer->status,
                'status_label' => AssetLifecycleStatus::TRANSFER_STATUSES[$transfer->status] ?? $transfer->status,
                'reason' => $transfer->reason,
                'notes' => $transfer->notes,
                'requested_by' => $transfer->requester?->name,
                'approved_by' => $transfer->approver?->name,
                'requested_at' => optional($transfer->requested_at)->format('Y-m-d'),
                'transferred_at' => optional($transfer->transferred_at)->format('Y-m-d'),
            ],
            'canManage' => (bool) auth()->user()?->hasPermission('transfers.manage'),
        ]);
    }

    private function notifyRequester(NotificationService $notifications, AssetTransfer $transfer, string $type, string $title, string $message): void
    {
        if (! $transfer->requester || $transfer->requested_by === auth()->id()) {
            return;
        }

        $notifications->send($transfer->requester, $type, $title, $message, [
            'transfer_id' => $transfer->id,
            'asset_id' => $transfer->asset_id,
        ]);
    }

    private function summary(): array
    {
        $counts = AssetTransfer::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'pending' => (int) ($counts['REQUESTED'] ?? 0),
            'approved' => (int) ($counts['APPROVED'] ?? 0),
            'completed' => (int) ($counts['COMPLETED'] ?? 0),
            'rejected' => (int) ($counts['REJECTED'] ?? 0),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'from_location_id' => ['nullable', 'exists:asset_locations,id'],
            'to_location_id' => ['nullable', 'exists:asset_locations,id'],
            'from_employee_id' => ['nullable', 'exists:employees,id'],
            'to_employee_id' => ['nullable', 'exists:employees,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'requested_at' => ['nullable', 'date'],
        ]);
    }
}
