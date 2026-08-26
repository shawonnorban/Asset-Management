<?php

namespace App\Http\Controllers\Depreciation;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\TaxDepreciationGroup;
use App\Models\AssetDepreciationSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\AuditTrailService;

class DepreciationSettingController extends Controller
{
    /**
     * ===============================
     * ASSET LIST + SETTING STATUS
     * ===============================
     */
    public function index()
    {
        $assets = Asset::with('depreciationSetting')->orderBy('asset_name')->get();
        return view('depreciation-settings.index', compact('assets'));
    }

    /**
     * ===============================
     * NEW SETTING FORM
     * ===============================
     */
    public function create()
    {
        $assets = Asset::doesntHave('depreciationSetting')
            ->orderBy('asset_name')
            ->get();

        $taxGroups = TaxDepreciationGroup::orderBy('name')->get();

        return view('depreciation-settings.create', compact('assets', 'taxGroups'));
    }

    /**
     * ===============================
     * STORE A DEPRECIATION SETTING
     * ===============================
     */
    public function store(
        Request $request,
        AuditTrailService $auditTrailService
    ) {
        $request->validate([
            'asset_id'                  => 'required|exists:assets,id|unique:asset_depreciation_settings,asset_id',
            'tax_depreciation_group_id' => 'required|exists:tax_depreciation_groups,id',
            'method'                    => 'required|in:STRAIGHT_LINE,DECLINING_BALANCE',
            'acquisition_cost'          => 'required|numeric|min:0',
            'salvage_value'             => 'nullable|numeric|min:0',
            'useful_life_months'        => 'nullable|integer|min:1',
            'in_service_date'           => 'required|date',
        ]);

        $setting = AssetDepreciationSetting::create([
            'asset_id'                  => $request->asset_id,
            'tax_depreciation_group_id' => $request->tax_depreciation_group_id,
            'method'                    => $request->method,
            'acquisition_cost'          => $request->acquisition_cost,
            'salvage_value'             => $request->salvage_value,
            'useful_life_months'        => $request->useful_life_months,
            'in_service_date'           => $request->in_service_date,
        ]);

        // =========================
        // AUDIT TRAIL (CREATE)
        // =========================
        $auditTrailService->log(
            action: 'CREATE_DEPRECIATION_SETTING',
            table: 'asset_depreciation_settings',
            rowId: $setting->id,
            message: "Created a depreciation setting for asset ID {$setting->asset_id}",
            before: null,
            after: $setting->toArray()
        );

        return redirect()
            ->route('depreciation-settings.index')
            ->with('success', 'Depreciation setting created successfully.');
    }

    /**
     * ===============================
     * EDIT SETTING FORM
     * ===============================
     */
    public function edit(Asset $asset)
    {
        $setting = $asset->depreciationSetting;

        if (!$setting) {
            abort(404, 'Depreciation setting not found.');
        }

        $taxGroups = TaxDepreciationGroup::orderBy('name')->get();

        return view('depreciation-settings.edit', compact('asset', 'setting', 'taxGroups'));
    }

    /**
     * ===============================
     * UPDATE SETTING
     * ===============================
     */
    public function update(
        Request $request,
        Asset $asset,
        AuditTrailService $auditTrailService
    ) {
        $setting = $asset->depreciationSetting;

        if (!$setting) {
            abort(404, 'Depreciation setting not found.');
        }

        $request->validate([
            'tax_depreciation_group_id' => 'required|exists:tax_depreciation_groups,id',
            'method'                    => 'required|in:STRAIGHT_LINE,DECLINING_BALANCE',
            'acquisition_cost'          => 'required|numeric|min:0',
            'salvage_value'             => 'nullable|numeric|min:0',
            'useful_life_months'        => 'nullable|integer|min:1',
            'in_service_date'           => 'required|date',
        ]);

        // =========================
        // BEFORE STATE
        // =========================
        $before = $setting->toArray();

        $setting->update([
            'tax_depreciation_group_id' => $request->tax_depreciation_group_id,
            'method'                    => $request->method,
            'acquisition_cost'          => $request->acquisition_cost,
            'salvage_value'             => $request->salvage_value,
            'useful_life_months'        => $request->useful_life_months,
            'in_service_date'           => $request->in_service_date,
        ]);

        // =========================
        // AFTER STATE
        // =========================
        $auditTrailService->log(
            action: 'UPDATE_DEPRECIATION_SETTING',
            table: 'asset_depreciation_settings',
            rowId: $setting->id,
            message: "Updated the depreciation setting for asset ID {$setting->asset_id}",
            before: $before,
            after: $setting->fresh()->toArray()
        );

        return redirect()
            ->route('depreciation-settings.index')
            ->with('success', 'Depreciation setting updated successfully.');
    }
}
