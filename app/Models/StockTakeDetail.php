<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTakeDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_take_details';

    protected $fillable = [
        'stock_take_id',
        'asset_id',
        'physical_status',
        'location_id',
        'employee_id',
        'note',
        'user_id',
    ];

    /**
     * ======================
     * RELATIONSHIPS
     * ======================
     */

    // Detail belongs to one stock take
    public function stockTake()
    {
        return $this->belongsTo(StockTake::class, 'stock_take_id');
    }

    // Detail refers to an asset
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    // Employee holding the asset
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // User who recorded the stock take result
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
