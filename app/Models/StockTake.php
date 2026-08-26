<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTake extends Model
{
    use HasFactory;

    protected $table = 'stock_takes';

    protected $fillable = [
        'stock_take_code',
        'name',
        'stock_take_date',
        'status',
        'user_id',
    ];

    protected $casts = [
        'stock_take_date' => 'date',
    ];

    /**
     * ======================
     * RELATIONSHIPS
     * ======================
     */

    // Stock take is created by a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // One stock take has many asset details
    public function details()
    {
        return $this->hasMany(StockTakeDetail::class, 'stock_take_id');
    }
}
