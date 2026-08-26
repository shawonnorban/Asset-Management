<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\AssetCategory;
use App\Models\User;
use App\Models\IssueReport;
use App\Models\AuditLog;
use App\Models\StockTake;
use App\Models\AssetDepreciationSetting;
use App\Models\MonthlyDepreciation;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role->role;

        // =========================
        // GENERAL DATA (ALL ROLES)
        // =========================
        $totalAssets     = Asset::count();
        $totalLocations  = AssetLocation::count();
        $totalCategories = AssetCategory::count();

        // =========================
        // ADMIN
        // =========================
        $totalAccounts = User::count();

        $auditLogs = AuditLog::orderByDesc('occurred_at')
            ->limit(5)
            ->get();

        $usersStatus = User::with('role')
            ->orderBy('name')
            ->take(4)
            ->get()
            ->map(function ($user) {
                $user->is_online = Cache::has('user-is-online-' . $user->id);
                return $user;
            });

        $incomingReports = IssueReport::whereIn('status', [
            'Pending',
            'In Review'
        ])->count();

        // =========================
        // STAFF
        // =========================
        $staffReports = IssueReport::with(['asset.location'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $totalStockTakes = StockTake::count();

        // =========================
        // MANAGER
        // =========================
        $totalDepreciations = null;
        $incomingReports    = collect();
        $latestDepreciations = collect();

        if ($role === 'manager') {

            // assets that have a depreciation setting
            $totalDepreciations = AssetDepreciationSetting::count();

            $totalStockTakes = StockTake::count();

            // incoming reports (limit 5)
            $incomingReports = IssueReport::with('asset')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get();

            // ==============================
            // Get ALL assets with their latest depreciation
            // ==============================
            $latestDepreciations = Asset::with([
                    'monthlyDepreciations' => function ($query) {
                        $query->orderByDesc('period');
                    }
                ])
                ->whereHas('monthlyDepreciations') // only assets that have been depreciated
                ->get();
        }

        return view('home', compact(
            // general
            'totalAssets',
            'totalLocations',
            'totalCategories',

            // admin
            'totalAccounts',
            'auditLogs',
            'usersStatus',
            'incomingReports',

            // staff
            'staffReports',
            'totalStockTakes',

            // manager
            'totalDepreciations',
            'latestDepreciations'
        ));
    }
}
