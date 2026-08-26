<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLocation extends Model
{
    use HasFactory;

    protected $table = 'asset_locations';

    protected $fillable = [
        'location_name',
    ];

    /**
     * RELATION
     * One location has many assets
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'location_id');
    }
}
