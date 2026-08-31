<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDepreciationSetting;
use App\Models\AssetLocation;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\AssetAssignment;
use App\Models\AssetDisposal;
use App\Models\AssetLifecycleLog;
use App\Models\AssetTransfer;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRequest;
use App\Models\Warranty;
use App\Services\ReportService;
use App\Support\AssetLifecycleStatus;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(ReportService $reports)
    {
        $user = auth()->user();
        $role = $user->canonicalRole();
        $isEmployee = $role === 'employee';
        $employeeId = $isEmployee ? $user->employee?->id : null;

        // Fail closed: an employee whose account is not linked to an employee
        // profile must see none of the estate, not all of it. Matches AssetExport.
        $assetScope = fn ($query) => $isEmployee ? $query->where('employee_id', $employeeId ?? 0) : $query;

        $assetSummary = $assetScope(Asset::query())
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN employee_id IS NOT NULL THEN 1 ELSE 0 END) as in_use, SUM(CASE WHEN employee_id IS NULL THEN 1 ELSE 0 END) as in_storage')
            ->first();
        $totalAssets = (int) ($assetSummary->total ?? 0);
        $totalLocations = $isEmployee ? 0 : AssetLocation::count();
        $totalCategories = $isEmployee ? 0 : AssetCategory::count();
        $totalAccounts = User::count();
        $inUseAssets = $isEmployee ? $totalAssets : (int) ($assetSummary->in_use ?? 0);
        $inStorageAssets = $isEmployee ? 0 : (int) ($assetSummary->in_storage ?? 0);
        $openMaintenance = MaintenanceRecord::whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])->count();
        $completedMaintenance = MaintenanceRecord::where('status', 'COMPLETED')->count();

        $auditLogs = AuditLog::orderByDesc('occurred_at')->limit(5)->get();
        $usersStatus = User::with('role')->orderBy('name')->take(4)->get()->map(function ($user) {
            $user->is_online = Cache::has('user-is-online-' . $user->id);
            return $user;
        });
        $recentAssignments = AssetAssignment::with(['asset', 'employee'])
            ->when($isEmployee, fn ($query) => $query->where('employee_id', $employeeId ?? 0))
            ->latest('assigned_at')->take(5)->get();
        $intakeStart = now()->subMonths(5)->startOfMonth();
        $intakeCounts = $assetScope(Asset::query())
            ->selectRaw('YEAR(added_date) as year, MONTH(added_date) as month, COUNT(*) as total')
            ->whereBetween('added_date', [$intakeStart, now()->endOfMonth()])
            ->groupByRaw('YEAR(added_date), MONTH(added_date)')
            ->get()
            ->keyBy(fn ($row) => $row->year . '-' . $row->month);
        $assetIntake = collect(range(5, 0))->map(function ($monthsAgo) use ($intakeCounts) {
            $date = now()->subMonths($monthsAgo);
            $count = $intakeCounts->get($date->year . '-' . $date->month);
            return ['label' => $date->format('M'), 'value' => (int) ($count->total ?? 0)];
        })->values();

        $totalDepreciations = null;
        $incomingReports = collect();
        $latestDepreciations = collect();

        if ($role === 'management') {
            $totalDepreciations = AssetDepreciationSetting::count();
            $latestDepreciations = Asset::with([
                'monthlyDepreciations' => fn ($query) => $query->orderByDesc('period'),
            ])->whereHas('monthlyDepreciations')->get();
        }

        $can = [
            'maintenance' => $user->hasPermission('maintenance.view'),
            'transfers' => $user->hasPermission('transfers.view'),
            'disposals' => $user->hasPermission('disposals.view'),
            'notifications' => $user->hasPermission('notifications.view'),
            'reports' => $user->hasPermission('reports.view'),
        ];

        return Inertia::render('dashboard', [
            'role' => $role,
            'can' => $can,
            'portfolio' => $this->portfolio($assetScope, $isEmployee),
            'upcomingMaintenance' => $this->upcomingMaintenance($can),
            'lifecycle' => $this->lifecycleSnapshot($can, $reports),
            'attention' => $this->attentionItems($can, $reports),
            'recentLifecycle' => $this->recentLifecycle($can),
            'unreadNotifications' => $can['notifications'] ? $user->notifications()->where('is_read', false)->count() : 0,
            'stats' => [
                'assets' => $totalAssets,
                'locations' => $totalLocations,
                'categories' => $totalCategories,
                'accounts' => $totalAccounts,
                'depreciations' => $totalDepreciations,
                'in_use' => $inUseAssets, 'in_storage' => $inStorageAssets,
                'open_maintenance' => $openMaintenance, 'completed_maintenance' => $completedMaintenance,
            ],
            'auditLogs' => $auditLogs->map(fn ($log) => [
                'id' => $log->id,
                'user' => $log->user_name ?? 'System',
                'action' => $log->action,
                'time' => optional($log->occurred_at)->format('d M Y H:i'),
            ])->values(),
            'usersStatus' => $usersStatus->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role_label,
                'online' => (bool) $user->is_online,
                'last_login' => $user->last_login_at?->format('d M Y H:i'),
            ])->values(),
            'latestDepreciations' => $latestDepreciations->map(fn ($asset) => [
                'id' => $asset->id,
                'name' => $asset->asset_name,
                'code' => $asset->asset_code,
                'period' => optional($asset->monthlyDepreciations->first())->period,
            ])->values(),
            'recentAssignments' => $recentAssignments->map(fn ($assignment) => [
                'id' => $assignment->id, 'asset_code' => $assignment->asset?->asset_code,
                'employee' => $assignment->employee?->name, 'date' => optional($assignment->assigned_at)->format('d M Y'),
            ])->values(),
            'assetIntake' => $assetIntake,
        ]);
    }

    /**
     * How the estate actually breaks down: by working state, by condition, and
     * across categories and locations. Scoped to the viewer's own assets when
     * they are an employee.
     *
     * @param  callable  $scope
     */
    private function portfolio($scope, bool $isEmployee): array
    {
        $byStatus = $scope(Asset::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byCondition = $scope(Asset::query())
            ->selectRaw('`condition`, COUNT(*) as total')
            ->groupBy('condition')
            ->pluck('total', 'condition');

        // Assets still in service that nobody has recorded a warranty against -
        // every one of them is an unbudgeted repair waiting to happen.
        $uncovered = $scope(Asset::query())
            ->whereNotIn('status', ['DISPOSED', 'RETIRED'])
            ->whereDoesntHave('warranties')
            ->count();

        return [
            'status' => collect(AssetController::STATUSES)
                ->map(fn ($label, $key) => [
                    'key' => $key,
                    'label' => $label,
                    'count' => (int) ($byStatus[$key] ?? 0),
                ])
                ->values()
                ->all(),
            'condition' => collect(AssetController::CONDITIONS)
                ->map(fn ($label, $key) => [
                    'key' => $key,
                    'label' => $label,
                    'count' => (int) ($byCondition[$key] ?? 0),
                ])
                ->values()
                ->all(),
            'categories' => $isEmployee ? [] : $this->groupedCounts(
                AssetCategory::withCount('assets')->orderByDesc('assets_count')->limit(6)->get(),
                'category_name',
            ),
            'locations' => $isEmployee ? [] : $this->groupedCounts(
                AssetLocation::withCount('assets')->orderByDesc('assets_count')->limit(6)->get(),
                'location_name',
            ),
            'uncovered' => $uncovered,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function groupedCounts($records, string $nameColumn): array
    {
        return $records
            ->filter(fn ($record) => $record->assets_count > 0)
            ->map(fn ($record) => [
                'id' => $record->id,
                'label' => $record->{$nameColumn},
                'count' => (int) $record->assets_count,
            ])
            ->values()
            ->all();
    }

    /**
     * Work booked in for the coming week, so the day can be planned from here.
     *
     * @param  array<string, bool>  $can
     * @return array<int, array<string, mixed>>
     */
    private function upcomingMaintenance(array $can): array
    {
        if (! $can['maintenance']) {
            return [];
        }

        return MaintenanceRequest::with(['asset', 'assignee'])
            ->whereIn('status', ReportService::OPEN_MAINTENANCE_STATUSES)
            ->whereNotNull('scheduled_at')
            ->whereDate('scheduled_at', '>=', today())
            ->whereDate('scheduled_at', '<=', today()->addDays(7))
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get()
            ->map(fn ($request) => [
                'id' => $request->id,
                'title' => $request->title,
                'asset_code' => $request->asset?->asset_code,
                'priority' => $request->priority,
                'assigned_to' => $request->assignee?->name,
                'scheduled_at' => optional($request->scheduled_at)->format('d M Y'),
                'days_away' => (int) today()->diffInDays($request->scheduled_at, false),
            ])
            ->values()
            ->all();
    }

    /**
     * Live counters for the lifecycle modules. Each key is null when the viewer
     * cannot see that module, so the page never renders a figure it would then
     * refuse to open.
     *
     * @param  array<string, bool>  $can
     */
    private function lifecycleSnapshot(array $can, ReportService $reports): array
    {
        $snapshot = ['maintenance' => null, 'warranty' => null, 'transfers' => null, 'disposals' => null];

        if ($can['maintenance']) {
            $open = MaintenanceRequest::whereIn('status', ReportService::OPEN_MAINTENANCE_STATUSES);

            $snapshot['maintenance'] = [
                'open' => (clone $open)->count(),
                'overdue' => (clone $open)->whereNotNull('scheduled_at')->whereDate('scheduled_at', '<', today())->count(),
                'legacy_open' => MaintenanceRecord::whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])->count(),
            ];

            $snapshot['warranty'] = [
                'expiring' => Warranty::expiringWithin()->count(),
                'expired' => Warranty::expiredCover()->count(),
            ];
        }

        if ($can['transfers']) {
            $snapshot['transfers'] = [
                'pending' => AssetTransfer::where('status', 'REQUESTED')->count(),
                'overdue' => $reports->overdueTransfers()->count(),
            ];
        }

        if ($can['disposals']) {
            $snapshot['disposals'] = [
                'pending' => AssetDisposal::where('status', 'REQUESTED')->count(),
                'disposed' => AssetDisposal::where('status', 'DISPOSED')->count(),
                'value_recovered' => (float) AssetDisposal::where('status', 'DISPOSED')->sum('value_recovered'),
            ];
        }

        return $snapshot;
    }

    /**
     * Things that need somebody to act today, worst first. Only non-zero items
     * are returned, so an empty array means the estate is clear.
     *
     * @param  array<string, bool>  $can
     * @return array<int, array<string, mixed>>
     */
    private function attentionItems(array $can, ReportService $reports): array
    {
        $items = [];

        if ($can['maintenance']) {
            $expired = Warranty::expiredCover()->count();
            $overdue = MaintenanceRequest::whereIn('status', ReportService::OPEN_MAINTENANCE_STATUSES)
                ->whereNotNull('scheduled_at')
                ->whereDate('scheduled_at', '<', today())
                ->count();
            $expiring = Warranty::expiringWithin()->count();

            if ($expired) {
                $items[] = ['tone' => 'danger', 'count' => $expired, 'label' => 'warranties have lapsed', 'href' => '/warranties'];
            }

            if ($overdue) {
                $items[] = ['tone' => 'danger', 'count' => $overdue, 'label' => 'maintenance jobs are overdue', 'href' => '/maintenance-requests'];
            }

            if ($expiring) {
                $items[] = ['tone' => 'warning', 'count' => $expiring, 'label' => 'warranties expire within 30 days', 'href' => '/warranties'];
            }
        }

        if ($can['transfers'] && $overdueTransfers = $reports->overdueTransfers()->count()) {
            $items[] = ['tone' => 'warning', 'count' => $overdueTransfers, 'label' => 'transfers have been pending over a week', 'href' => '/transfers'];
        }

        if ($can['disposals'] && $pendingDisposals = AssetDisposal::where('status', 'REQUESTED')->count()) {
            $items[] = ['tone' => 'info', 'count' => $pendingDisposals, 'label' => 'disposal requests await approval', 'href' => '/disposals'];
        }

        return $items;
    }

    /**
     * @param  array<string, bool>  $can
     * @return array<int, array<string, mixed>>
     */
    private function recentLifecycle(array $can): array
    {
        if (! $can['maintenance'] && ! $can['transfers'] && ! $can['disposals']) {
            return [];
        }

        return AssetLifecycleLog::with(['asset', 'user'])
            ->orderByDesc('event_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'asset_id' => $log->asset_id,
                'asset_code' => $log->asset?->asset_code,
                'event_type' => $log->event_type,
                'event_label' => AssetLifecycleStatus::LIFECYCLE_EVENTS[$log->event_type] ?? $log->event_type,
                'description' => $log->description,
                'user' => $log->user?->name,
                'time' => optional($log->event_at)->format('d M Y, h:i A'),
            ])
            ->values()
            ->all();
    }
}
