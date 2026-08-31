<?php

namespace App\Http\Controllers\Depreciation;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use App\Services\AuditTrailService;
use Inertia\Inertia;

class DepreciationDisposalController extends Controller
{
    /**
     * DISPOSAL FORM
     */
    public function create(Asset $asset)
    {
        $setting = $asset->depreciationSetting;

        if (!$setting) {
            return redirect()
                ->route('depreciation.show', $asset->id)
                ->with('error', 'There is no depreciation setting yet.');
        }

        if ($setting->is_disposed) {
            return redirect()
                ->route('depreciation.show', $asset->id)
                ->with('error', 'This asset has already been disposed.');
        }

        return Inertia::render('depreciation/disposal', [
            'title' => 'Dispose Asset', 'asset' => $asset, 'setting' => $setting,
        ]);
    }

    /**
     * STORE DISPOSAL
     */
    public function store(
        Request $request,
        Asset $asset,
        AuditTrailService $auditTrailService
    ) {
        $setting = $asset->depreciationSetting;

        if (!$setting || $setting->is_disposed) {
            return redirect()->route('depreciation.show', $asset->id);
        }

        $request->validate([
            'disposal_reason' => 'required|in:DAMAGED,SOLD,DONATED,LOST,OTHER',
            'disposal_note'   => 'nullable|string|max:500',
        ]);

        // =========================
        // BEFORE STATE
        // =========================
        $before = $setting->toArray();

        $setting->update([
            'is_disposed'     => true,
            'disposal_reason' => $request->disposal_reason,
            'disposal_note'   => $request->disposal_note,
        ]);

        // =========================
        // AUDIT TRAIL (DISPOSAL)
        // =========================
        $auditTrailService->log(
            action: 'DISPOSE_ASSET',
            table: 'asset_depreciation_settings',
            rowId: $setting->id,
            message: "Disposed asset ID {$setting->asset_id} with reason {$request->disposal_reason}",
            before: $before,
            after: $setting->fresh()->toArray()
        );

        return redirect()
            ->route('depreciation.show', $asset->id)
            ->with('success', 'Asset disposed successfully.');
    }
}
