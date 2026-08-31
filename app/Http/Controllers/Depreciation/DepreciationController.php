<?php

namespace App\Http\Controllers\Depreciation;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\MonthlyDepreciation;
use App\Models\AssetDepreciationSetting;
use App\Services\DepreciationService;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class DepreciationController extends Controller
{
    /**
     * Asset list with depreciation status
     */
    public function index()
    {
        $assets = Asset::with([
            'depreciationSetting',
            'monthlyDepreciations' => function ($q) {
                $q->latest('period');
            }
        ])->get();

        return Inertia::render('depreciation/index', [
            'title' => 'Depreciation List', 'description' => 'Monitor asset depreciation and monthly history.',
            'rows' => $assets->map(fn ($asset) => [
                'id' => $asset->id, 'code' => $asset->asset_code, 'name' => $asset->asset_name,
                'cost' => $asset->depreciationSetting?->acquisition_cost ?? '-',
                'status' => $asset->depreciationSetting ? 'Configured' : 'Not configured',
            ])->values(), 'detail' => true,
        ]);
    }

    /**
     * Depreciation detail per asset
     */
    public function show(Asset $asset)
    {
        $asset->load([
            'category',
            'location',
            'depreciationSetting.taxDepreciationGroup',
        ]);

        $setting = $asset->depreciationSetting;

        $history = MonthlyDepreciation::where('asset_id', $asset->id)
            ->orderBy('period', 'asc')
            ->get();

        return Inertia::render('depreciation/show', ['title' => $asset->asset_name, 'asset' => $asset, 'setting' => $setting, 'history' => $history]);
    }

    /**
     * Generate monthly depreciation (manual)
     * admin & manager ONLY
     */
    public function depreciate(
        Asset $asset,
        DepreciationService $depreciationService,
        AuditTrailService $auditTrailService
    ) {
        // =========================
        // Role check
        // =========================
        if (!auth()->user()->inRoles(['admin', 'manager'])) {
            abort(403, 'You do not have access.');
        }

        if (!$asset->depreciationSetting) {
            return back()->with('error', 'The depreciation setting has not been filled in yet.');
        }

        try {
            // =========================
            // GENERATE DEPRECIATION
            // =========================
            $depreciation = $depreciationService->generateMonthly(
                $asset,
                $asset->depreciationSetting,
                auth()->id()
            );

            // =========================
            // AUDIT TRAIL
            // =========================
            $auditTrailService->log(
                action: 'GENERATE_DEPRECIATION',
                table: 'monthly_depreciations',
                rowId: $depreciation->id,
                message: 'Generated depreciation for asset ' . $asset->asset_code .
                         ' period ' . $depreciation->period,
                before: null,
                after: $depreciation->toArray()
            );

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'This month\'s depreciation was created successfully.');
    }

    public function exportPdf($assetId)
    {
        $setting = AssetDepreciationSetting::with(['asset.category', 'asset.location', 'taxDepreciationGroup'])
            ->where('asset_id', $assetId)
            ->firstOrFail();

        $history = MonthlyDepreciation::where('asset_id', $assetId)
            ->orderBy('period')
            ->get();

        $data = [
            'asset'   => $setting->asset,
            'setting' => $setting,
            'history' => $history,
        ];

        $pdf = Pdf::loadView('depreciation.pdf', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->stream(
            'depreciation-report-' . $setting->asset->asset_code . '.pdf'
        );
    }
}
