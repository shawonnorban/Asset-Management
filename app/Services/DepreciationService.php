<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Asset;
use App\Models\MonthlyDepreciation;
use App\Models\AssetDepreciationSetting;

class DepreciationService
{
    /**
     * Generate one month of depreciation for an asset.
     * Triggered manually.
     */
    public function generateMonthly(
        Asset $asset,
        AssetDepreciationSetting $setting,
        int $userId
    ): MonthlyDepreciation {

        // =========================
        // 0. Check disposal status
        // =========================
        if ($setting->is_disposed) {
            throw new \Exception('This asset has been disposed. Depreciation cannot continue.');
        }

        // =========================
        // 1. Determine the period
        // =========================
        $last = MonthlyDepreciation::where('asset_id', $asset->id)
            ->orderByDesc('period')
            ->first();

        $period = $last
            ? Carbon::parse($last->period)->addMonth()->startOfMonth()
            : Carbon::parse($setting->in_service_date)->addMonth()->startOfMonth();

        // Prevent duplicate depreciation
        if (
            MonthlyDepreciation::where('asset_id', $asset->id)
                ->where('period', $period)
                ->exists()
        ) {
            throw new \Exception('Depreciation for this period already exists.');
        }

        // =========================
        // 2. Opening book value
        // =========================
        $openingBookValue = $last
            ? $last->ending_book_value
            : $setting->acquisition_cost;

        if ($openingBookValue <= 0) {
            throw new \Exception('The book value of this asset is already zero.');
        }

        // =========================
        // 3. Calculate the expense
        // =========================
        if ($setting->method === 'STRAIGHT_LINE') {
            $expense = $this->calculateStraightLine($setting);
        } else {
            $expense = $this->calculateDecliningBalance($openingBookValue, $setting);
        }

        // =========================
        // 4. Calculate closing values
        // =========================
        $endingBookValue = max(0, $openingBookValue - $expense);

        $accumulated = ($last?->accumulated_depreciation ?? 0) + $expense;

        // =========================
        // 5. Persist
        // =========================
        return MonthlyDepreciation::create([
            'asset_id'                 => $asset->id,
            'period'                   => $period,
            'method'                   => $setting->method,
            'monthly_expense'          => $expense,
            'accumulated_depreciation' => $accumulated,
            'ending_book_value'        => $endingBookValue,
            'user_id'                  => $userId,
        ]);
    }

    // ==================================================
    // DEPRECIATION FORMULAS
    // ==================================================

    /**
     * Straight line method
     */
    protected function calculateStraightLine(AssetDepreciationSetting $setting): float
    {
        $usefulLifeMonths = $setting->useful_life_months
            ?? ($setting->taxDepreciationGroup->useful_life_years * 12);

        $salvageValue = $setting->salvage_value ?? 0;

        return round(
            ($setting->acquisition_cost - $salvageValue) / $usefulLifeMonths,
            2
        );
    }

    /**
     * Declining balance method
     */
    protected function calculateDecliningBalance(
        float $openingBookValue,
        AssetDepreciationSetting $setting
    ): float {
        $annualRate = $setting->taxDepreciationGroup->declining_balance_rate;

        return round(
            ($openingBookValue * ($annualRate / 100)) / 12,
            2
        );
    }
}
