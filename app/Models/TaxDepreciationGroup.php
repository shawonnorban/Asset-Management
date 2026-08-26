<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxDepreciationGroup extends Model
{
    use HasFactory;

    protected $table = 'tax_depreciation_groups';

    // primary key is not an auto incrementing bigint
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'useful_life_years',
        'straight_line_rate',
        'declining_balance_rate',
    ];

    /**
     * One tax group can be used by many assets
     */
    public function assetDepreciationSettings()
    {
        return $this->hasMany(AssetDepreciationSetting::class, 'tax_depreciation_group_id');
    }
}
