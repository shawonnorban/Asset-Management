<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Services\AssetLifecycleService;
use App\Services\NotificationService;
use App\Support\AssetLifecycleStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DisposalController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'status' => (string) $request->query('status', ''),
            'asset_id' => (string) $request->query('asset_id', ''),
        ];

        $disposals = AssetDisposal::with(['asset', 'requester', 'approver'])
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['asset_id'], fn ($query, $assetId) => $query->where('asset_id', $assetId))
            ->latest('requested_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('disposals/index', [
            'title' => 'Asset Disposal',
            'description' => 'Track disposal requests, approval status, and recovery value.',
            'disposals' => $disposals->getCollection()->map(fn ($disposal) => [
                'id' => $disposal->id,
                'asset_id' => $disposal->asset_id,
                'asset_code' => $disposal->asset?->asset_code,
                'asset_name' => $disposal->asset?->asset_name,
                'status' => $disposal->status,
                'status_label' => AssetLifecycleStatus::DISPOSAL_STATUSES[$disposal->status] ?? $disposal->status,
                'reason' => $disposal->reason,
                'method' => $disposal->method,
                'value_recovered' => $disposal->value_recovered,
                'requested_at' => optional($disposal->requested_at)->format('d M Y'),
                'requested_by' => $disposal->requester?->name,
                'approved_by' => $disposal->approver?->name,
            ])->values(),
            'pagination' => $disposals->toArray(),
            'statuses' => AssetLifecycleStatus::DISPOSAL_STATUSES,
            'filters' => $filters,
            'summary' => $this->summary(),
            'canManage' => (bool) auth()->user()?->hasPermission('disposals.manage'),
        ]);
    }

    public function create()
    {
        return Inertia::render('disposals/form', [
            'title' => 'Request disposal',
            'disposal' => null,
            'assets' => Asset::orderBy('asset_code')->get()->map(fn ($asset) => ['id' => $asset->id, 'label' => $asset->asset_code . ' - ' . $asset->asset_name])->values()->all(),
            'statuses' => AssetLifecycleStatus::DISPOSAL_STATUSES,
        ]);
    }

    public function store(Request $request, AssetLifecycleService $lifecycle)
    {
        $data = $this->validated($request);

        $disposal = AssetDisposal::create([
            ...$data,
            'requested_by' => auth()->id(),
            'status' => 'REQUESTED',
            'requested_at' => $data['requested_at'] ?? today(),
        ]);

        $lifecycle->createLog(
            $disposal->asset,
            'DISPOSAL_REQUESTED',
            'Disposal requested for asset ' . ($disposal->asset?->asset_code ?? 'N/A'),
            null,
            ['disposal_id' => $disposal->id, 'status' => $disposal->status, 'reason' => $disposal->reason],
            auth()->id(),
        );

        return redirect()->route('disposals.show', $disposal)->with('success', 'Disposal request created.');
    }

    public function approve(Request $request, AssetDisposal $disposal, AssetLifecycleService $lifecycle, NotificationService $notifications)
    {
        $request->validate([
            'notes' => ['nullable', 'string'],
            'value_recovered' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! in_array($disposal->status, ['REQUESTED', 'APPROVED'], true)) {
            return back()->with('error', 'This disposal request cannot be approved in its current state.');
        }

        DB::transaction(function () use ($request, $disposal, $lifecycle) {
            $asset = $disposal->asset;
            $previousStatus = $disposal->status;
            $previousAssetStatus = $asset?->status;

            $disposal->update([
                'status' => 'DISPOSED',
                'approved_by' => auth()->id(),
                'notes' => $request->input('notes', $disposal->notes),
                'value_recovered' => $request->input('value_recovered', $disposal->value_recovered),
                'disposed_at' => $disposal->disposed_at ?? now()->toDateString(),
            ]);

            if ($asset) {
                $asset->update([
                    'status' => 'DISPOSED',
                    'employee_id' => null,
                ]);

                $lifecycle->createLog(
                    $asset,
                    'DISPOSAL_COMPLETED',
                    'Disposal approved for asset ' . $asset->asset_code,
                    ['status' => $previousStatus, 'asset_status' => $previousAssetStatus],
                    [
                        'status' => 'DISPOSED',
                        'asset_status' => 'DISPOSED',
                        'value_recovered' => $disposal->value_recovered,
                        'approved_by' => auth()->user()?->name,
                        'notes' => $disposal->notes,
                    ],
                    auth()->id(),
                );
            }
        });

        $this->notifyRequester(
            $notifications,
            $disposal,
            'disposal_approved',
            'Disposal approved',
            'Your disposal request for ' . ($disposal->asset?->asset_code ?? 'the asset') . ' has been approved.'
        );

        return redirect()->route('disposals.show', $disposal)->with('success', 'Disposal approved and asset marked as disposed.');
    }

    public function reject(Request $request, AssetDisposal $disposal, AssetLifecycleService $lifecycle, NotificationService $notifications)
    {
        $request->validate(['notes' => ['nullable', 'string']]);

        if ($disposal->status !== 'REQUESTED') {
            return back()->with('error', 'Only a pending disposal request can be rejected.');
        }

        $disposal->update([
            'status' => 'REJECTED',
            'approved_by' => auth()->id(),
            'notes' => $request->input('notes', $disposal->notes),
        ]);

        if ($disposal->asset) {
            $lifecycle->createLog(
                $disposal->asset,
                'DISPOSAL_REQUESTED',
                'Disposal rejected for asset ' . $disposal->asset->asset_code,
                ['status' => 'REQUESTED'],
                ['status' => 'REJECTED', 'approved_by' => auth()->user()?->name, 'notes' => $disposal->notes],
                auth()->id(),
            );
        }

        $this->notifyRequester(
            $notifications,
            $disposal,
            'disposal_rejected',
            'Disposal rejected',
            'Your disposal request for ' . ($disposal->asset?->asset_code ?? 'the asset') . ' has been rejected.'
        );

        return redirect()->route('disposals.show', $disposal)->with('success', 'Disposal request rejected.');
    }

    public function show(AssetDisposal $disposal)
    {
        $disposal->load(['asset', 'requester', 'approver']);

        return Inertia::render('disposals/show', [
            'title' => 'Disposal detail',
            'disposal' => [
                'id' => $disposal->id,
                'asset' => $disposal->asset ? ['id' => $disposal->asset->id, 'code' => $disposal->asset->asset_code, 'name' => $disposal->asset->asset_name] : null,
                'status' => $disposal->status,
                'status_label' => AssetLifecycleStatus::DISPOSAL_STATUSES[$disposal->status] ?? $disposal->status,
                'reason' => $disposal->reason,
                'method' => $disposal->method,
                'value_recovered' => $disposal->value_recovered,
                'notes' => $disposal->notes,
                'requested_by' => $disposal->requester?->name,
                'approved_by' => $disposal->approver?->name,
                'requested_at' => optional($disposal->requested_at)->format('Y-m-d'),
                'disposed_at' => optional($disposal->disposed_at)->format('Y-m-d'),
            ],
            'canManage' => (bool) auth()->user()?->hasPermission('disposals.manage'),
        ]);
    }

    private function notifyRequester(NotificationService $notifications, AssetDisposal $disposal, string $type, string $title, string $message): void
    {
        if (! $disposal->requester || $disposal->requested_by === auth()->id()) {
            return;
        }

        $notifications->send($disposal->requester, $type, $title, $message, [
            'disposal_id' => $disposal->id,
            'asset_id' => $disposal->asset_id,
        ]);
    }

    private function summary(): array
    {
        $counts = AssetDisposal::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'pending' => (int) ($counts['REQUESTED'] ?? 0),
            'approved' => (int) ($counts['APPROVED'] ?? 0),
            'disposed' => (int) ($counts['DISPOSED'] ?? 0),
            'rejected' => (int) ($counts['REJECTED'] ?? 0),
            'value_recovered' => (float) AssetDisposal::where('status', 'DISPOSED')->sum('value_recovered'),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'method' => ['nullable', 'string', 'max:80'],
            'value_recovered' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'requested_at' => ['nullable', 'date'],
        ]);
    }
}
