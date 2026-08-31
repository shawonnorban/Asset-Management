<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\AssetTransfer;
use App\Models\MaintenanceRequest;
use App\Models\Warranty;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * One source of truth for every commercial report, so the on-screen figures and
 * the exported file can never drift apart.
 */
class ReportService
{
    public const OPEN_MAINTENANCE_STATUSES = ['OPEN', 'IN_PROGRESS'];

    // =========================
    //     EXECUTIVE DASHBOARD
    // =========================

    /**
     * The headline cards. Every number here is a link to a deeper report.
     *
     * @return array<string, int|float>
     */
    public function executiveMetrics(): array
    {
        return [
            'active_assets' => Asset::whereNotIn('status', ['DISPOSED', 'RETIRED'])->count(),
            'assigned_assets' => Asset::whereNotNull('employee_id')->whereNotIn('status', ['DISPOSED', 'RETIRED'])->count(),
            'under_maintenance' => $this->assetsUnderMaintenance(),
            'warranty_alerts' => Warranty::expiringWithin()->count() + Warranty::expiredCover()->count(),
            'overdue_transfers' => $this->overdueTransfers()->count(),
            'disposed_assets' => AssetDisposal::where('status', 'DISPOSED')->count(),
            'value_recovered' => (float) AssetDisposal::where('status', 'DISPOSED')->sum('value_recovered'),
            'total_assets' => Asset::count(),
        ];
    }

    /**
     * Assets in the shop, counted across both the commercial request queue and
     * the older maintenance record table - either one means the asset is down.
     */
    public function assetsUnderMaintenance(): int
    {
        return Asset::where(function ($query) {
            $query->whereHas('maintenanceRequests', fn ($q) => $q->whereIn('status', self::OPEN_MAINTENANCE_STATUSES))
                ->orWhereHas('maintenanceRecords', fn ($q) => $q->whereIn('status', ['SCHEDULED', 'IN_PROGRESS']));
        })->count();
    }

    /** Transfers approved but still not settled after a week, or never approved. */
    public function overdueTransfers(int $days = 7): Collection
    {
        return AssetTransfer::with(['asset', 'requester', 'fromLocation', 'toLocation'])
            ->whereIn('status', ['REQUESTED', 'IN_TRANSIT'])
            ->whereDate('requested_at', '<', today()->subDays($days))
            ->orderBy('requested_at')
            ->get();
    }

    // =========================
    //     6.1 MAINTENANCE
    // =========================

    /**
     * @return array{summary: array<string, int|float>, monthly_cost: array<int, array<string, mixed>>, overdue: array<int, array<string, mixed>>, rows: array<int, array<string, mixed>>}
     */
    public function maintenanceReport(): array
    {
        $requests = MaintenanceRequest::with(['asset', 'requester', 'assignee'])->get();

        $open = $requests->whereIn('status', self::OPEN_MAINTENANCE_STATUSES);
        $overdue = $open->filter(fn ($request) => $request->scheduled_at && $request->scheduled_at->lt(today()));
        $completed = $requests->where('status', 'COMPLETED');

        return [
            'summary' => [
                'open' => $open->count(),
                'in_progress' => $requests->where('status', 'IN_PROGRESS')->count(),
                'overdue' => $overdue->count(),
                'completed' => $completed->count(),
                'total_cost' => round((float) $requests->sum(fn ($request) => (float) $request->actual_cost), 2),
                'average_cost' => $completed->count()
                    ? round((float) $completed->sum(fn ($request) => (float) $request->actual_cost) / $completed->count(), 2)
                    : 0.0,
            ],
            'monthly_cost' => $this->monthlyMaintenanceCost($requests),
            'overdue' => $overdue->map(fn ($request) => [
                'id' => $request->id,
                'title' => $request->title,
                'asset_code' => $request->asset?->asset_code,
                'scheduled_at' => optional($request->scheduled_at)->format('d M Y'),
                'days_overdue' => (int) $request->scheduled_at->diffInDays(today()),
                'priority' => $request->priority,
                'assigned_to' => $request->assignee?->name,
            ])->values()->all(),
            'rows' => $requests->sortByDesc('requested_at')->map(fn ($request) => [
                'id' => $request->id,
                'title' => $request->title,
                'asset_code' => $request->asset?->asset_code,
                'asset_name' => $request->asset?->asset_name,
                'maintenance_type' => $request->maintenance_type,
                'priority' => $request->priority,
                'status' => $request->status,
                'vendor_name' => $request->vendor_name,
                'requested_at' => optional($request->requested_at)->format('d M Y'),
                'scheduled_at' => optional($request->scheduled_at)->format('d M Y'),
                'completed_at' => optional($request->completed_at)->format('d M Y'),
                'estimated_cost' => (float) $request->estimated_cost,
                'actual_cost' => (float) $request->actual_cost,
            ])->values()->all(),
        ];
    }

    /** Cost per month for the last 12 months, keyed by completion month. */
    private function monthlyMaintenanceCost(Collection $requests, int $months = 12): array
    {
        $window = collect(range($months - 1, 0))
            ->mapWithKeys(fn ($offset) => [now()->startOfMonth()->subMonths($offset)->format('Y-m') => 0.0]);

        foreach ($requests as $request) {
            $date = $request->completed_at ?? $request->requested_at;

            if (! $date) {
                continue;
            }

            $key = Carbon::parse($date)->format('Y-m');

            if ($window->has($key)) {
                $window[$key] = round($window[$key] + (float) ($request->actual_cost ?: $request->estimated_cost), 2);
            }
        }

        return $window->map(fn ($cost, $month) => [
            'month' => Carbon::createFromFormat('!Y-m', $month)->format('M Y'),
            'key' => $month,
            'cost' => $cost,
        ])->values()->all();
    }

    // =========================
    //     6.2 WARRANTY
    // =========================

    /**
     * @return array{summary: array<string, int>, expiring: array<int, array<string, mixed>>, expired: array<int, array<string, mixed>>, vendors: array<int, array<string, mixed>>, rows: array<int, array<string, mixed>>}
     */
    public function warrantyReport(int $days = Warranty::WARNING_DAYS): array
    {
        $warranties = Warranty::with('asset')->get();

        $expiring = $warranties->filter(fn ($warranty) => $warranty->end_date
            && $warranty->end_date->gte(today())
            && $warranty->end_date->lte(today()->addDays($days))
            && $warranty->status !== 'VOID');

        $expired = $warranties->filter(fn ($warranty) => $warranty->end_date
            && $warranty->end_date->lt(today())
            && $warranty->status !== 'VOID');

        return [
            'summary' => [
                'total' => $warranties->count(),
                'active' => $warranties->where('status', 'ACTIVE')->count(),
                'expiring_soon' => $expiring->count(),
                'expired' => $expired->count(),
                'claimed' => $warranties->where('claim_status', 'CLAIMED')->count(),
            ],
            'expiring' => $expiring->sortBy('end_date')->map(fn ($warranty) => $this->warrantyRow($warranty))->values()->all(),
            'expired' => $expired->sortByDesc('end_date')->map(fn ($warranty) => $this->warrantyRow($warranty))->values()->all(),
            'vendors' => $this->vendorClaimBreakdown($warranties),
            'rows' => $warranties->sortBy('end_date')->map(fn ($warranty) => $this->warrantyRow($warranty))->values()->all(),
        ];
    }

    private function warrantyRow(Warranty $warranty): array
    {
        return [
            'id' => $warranty->id,
            'asset_code' => $warranty->asset?->asset_code,
            'asset_name' => $warranty->asset?->asset_name,
            'vendor_name' => $warranty->vendor_name,
            'warranty_type' => $warranty->warranty_type,
            'start_date' => optional($warranty->start_date)->format('d M Y'),
            'end_date' => optional($warranty->end_date)->format('d M Y'),
            'days_remaining' => $warranty->days_remaining,
            'status' => $warranty->status,
            'claim_status' => $warranty->claim_status,
        ];
    }

    /** Vendor-wise claim tracking: who covers what, and how often it is claimed. */
    private function vendorClaimBreakdown(Collection $warranties): array
    {
        return $warranties->groupBy(fn ($warranty) => $warranty->vendor_name ?: 'Unspecified vendor')
            ->map(fn (Collection $group, string $vendor) => [
                'vendor_name' => $vendor,
                'total' => $group->count(),
                'active' => $group->where('status', 'ACTIVE')->count(),
                'expired' => $group->where('status', 'EXPIRED')->count(),
                'claims_open' => $group->where('claim_status', 'IN_PROGRESS')->count(),
                'claims_settled' => $group->where('claim_status', 'CLAIMED')->count(),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    // =========================
    //     6.3 TRANSFER / DISPOSAL
    // =========================

    /**
     * @return array{summary: array<string, int|float>, transfer_reasons: array<int, array<string, mixed>>, disposal_reasons: array<int, array<string, mixed>>, transfers: array<int, array<string, mixed>>, disposals: array<int, array<string, mixed>>}
     */
    public function movementReport(): array
    {
        $transfers = AssetTransfer::with(['asset', 'requester', 'approver', 'fromLocation', 'toLocation'])->get();
        $disposals = AssetDisposal::with(['asset', 'requester', 'approver'])->get();

        $completedTransfers = $transfers->whereIn('status', ['APPROVED', 'COMPLETED']);
        $completedDisposals = $disposals->where('status', 'DISPOSED');

        return [
            'summary' => [
                'transfers_total' => $transfers->count(),
                'transfers_completed' => $completedTransfers->count(),
                'transfers_pending' => $transfers->where('status', 'REQUESTED')->count(),
                'disposals_total' => $disposals->count(),
                'disposals_completed' => $completedDisposals->count(),
                'disposals_pending' => $disposals->where('status', 'REQUESTED')->count(),
                'value_recovered' => round((float) $completedDisposals->sum(fn ($disposal) => (float) $disposal->value_recovered), 2),
            ],
            'transfer_reasons' => $this->reasonSummary($transfers),
            'disposal_reasons' => $this->reasonSummary($disposals),
            'transfers' => $transfers->sortByDesc('requested_at')->map(fn ($transfer) => [
                'id' => $transfer->id,
                'asset_code' => $transfer->asset?->asset_code,
                'asset_name' => $transfer->asset?->asset_name,
                'from' => $transfer->fromLocation?->location_name,
                'to' => $transfer->toLocation?->location_name,
                'status' => $transfer->status,
                'reason' => $transfer->reason,
                'requested_by' => $transfer->requester?->name,
                'approved_by' => $transfer->approver?->name,
                'requested_at' => optional($transfer->requested_at)->format('d M Y'),
                'transferred_at' => optional($transfer->transferred_at)->format('d M Y'),
            ])->values()->all(),
            'disposals' => $disposals->sortByDesc('requested_at')->map(fn ($disposal) => [
                'id' => $disposal->id,
                'asset_code' => $disposal->asset?->asset_code,
                'asset_name' => $disposal->asset?->asset_name,
                'status' => $disposal->status,
                'reason' => $disposal->reason,
                'method' => $disposal->method,
                'value_recovered' => (float) $disposal->value_recovered,
                'requested_by' => $disposal->requester?->name,
                'approved_by' => $disposal->approver?->name,
                'requested_at' => optional($disposal->requested_at)->format('d M Y'),
                'disposed_at' => optional($disposal->disposed_at)->format('d M Y'),
            ])->values()->all(),
        ];
    }

    /** Why assets move or leave the estate, most common reason first. */
    private function reasonSummary(Collection $records): array
    {
        return $records->groupBy(fn ($record) => trim((string) $record->reason) ?: 'Not stated')
            ->map(fn (Collection $group, string $reason) => [
                'reason' => $reason,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    // =========================
    //     EXPORT SHAPING
    // =========================

    /**
     * Flatten a report into headings plus rows, the shape every export format needs.
     *
     * @return array{title: string, headings: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    public function exportable(string $type): array
    {
        return match ($type) {
            'maintenance' => $this->exportableMaintenance(),
            'warranty' => $this->exportableWarranty(),
            'movement' => $this->exportableMovement(),
            default => throw new \InvalidArgumentException('Unknown report type: ' . $type),
        };
    }

    private function exportableMaintenance(): array
    {
        return [
            'title' => 'Maintenance report',
            'headings' => ['Asset code', 'Asset', 'Title', 'Type', 'Priority', 'Status', 'Vendor', 'Requested', 'Scheduled', 'Completed', 'Estimated cost', 'Actual cost'],
            'rows' => collect($this->maintenanceReport()['rows'])->map(fn ($row) => [
                $row['asset_code'] ?? '-',
                $row['asset_name'] ?? '-',
                $row['title'],
                $row['maintenance_type'] ?? '-',
                $row['priority'],
                $row['status'],
                $row['vendor_name'] ?? '-',
                $row['requested_at'] ?? '-',
                $row['scheduled_at'] ?? '-',
                $row['completed_at'] ?? '-',
                $row['estimated_cost'],
                $row['actual_cost'],
            ])->values()->all(),
        ];
    }

    private function exportableWarranty(): array
    {
        return [
            'title' => 'Warranty report',
            'headings' => ['Asset code', 'Asset', 'Vendor', 'Type', 'Start', 'End', 'Days remaining', 'Status', 'Claim status'],
            'rows' => collect($this->warrantyReport()['rows'])->map(fn ($row) => [
                $row['asset_code'] ?? '-',
                $row['asset_name'] ?? '-',
                $row['vendor_name'] ?? '-',
                $row['warranty_type'] ?? '-',
                $row['start_date'] ?? '-',
                $row['end_date'] ?? '-',
                $row['days_remaining'] ?? '-',
                $row['status'],
                $row['claim_status'] ?? '-',
            ])->values()->all(),
        ];
    }

    private function exportableMovement(): array
    {
        $report = $this->movementReport();

        $transfers = collect($report['transfers'])->map(fn ($row) => [
            'Transfer',
            $row['asset_code'] ?? '-',
            $row['asset_name'] ?? '-',
            $row['from'] ?? '-',
            $row['to'] ?? '-',
            $row['status'],
            $row['reason'] ?? '-',
            $row['requested_by'] ?? '-',
            $row['approved_by'] ?? '-',
            $row['requested_at'] ?? '-',
            $row['transferred_at'] ?? '-',
            0,
        ]);

        $disposals = collect($report['disposals'])->map(fn ($row) => [
            'Disposal',
            $row['asset_code'] ?? '-',
            $row['asset_name'] ?? '-',
            '-',
            $row['method'] ?? '-',
            $row['status'],
            $row['reason'] ?? '-',
            $row['requested_by'] ?? '-',
            $row['approved_by'] ?? '-',
            $row['requested_at'] ?? '-',
            $row['disposed_at'] ?? '-',
            $row['value_recovered'],
        ]);

        return [
            'title' => 'Transfer and disposal report',
            'headings' => ['Movement', 'Asset code', 'Asset', 'From', 'To', 'Status', 'Reason', 'Requested by', 'Approved by', 'Requested', 'Settled', 'Value recovered'],
            'rows' => $transfers->concat($disposals)->values()->all(),
        ];
    }
}
