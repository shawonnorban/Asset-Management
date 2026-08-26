<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $table = 'asset_categories';

    protected $fillable = [
        'category_name',
        'asset_type',
    ];

    /** Asset types a category can drive, with their display labels. */
    public const ASSET_TYPES = [
        'COMPUTER'       => 'Computer (desktop / laptop)',
        'PERIPHERAL'     => 'Peripheral (monitor, UPS, keyboard...)',
        'PRINTER'        => 'Printer / scanner',
        'NETWORK_DEVICE' => 'Network device (router, switch, server)',
        'OTHER'          => 'Other (no detailed specification)',
    ];

    /**
     * RELATION
     * One category has many assets
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
