<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDepreciationSetting extends Model
{
    use HasFactory;

    protected $table = 'asset_depreciation_settings';

    protected $fillable = [
        'asset_id',
        'tax_depreciation_group_id',
        'method',
        'acquisition_cost',
        'salvage_value',
        'useful_life_months',
        'is_disposed',
        'in_service_date',
        'disposal_reason',
        'disposal_note',
    ];

    protected $casts = [
        'acquisition_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'is_disposed' => 'boolean',
        'in_service_date' => 'date',
    ];

    /**
     * Relation to Asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Relation to tax depreciation group
     */
    public function taxDepreciationGroup()
    {
        return $this->belongsTo(TaxDepreciationGroup::class, 'tax_depreciation_group_id');
    }
}
