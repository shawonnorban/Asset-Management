<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyDepreciation extends Model
{
    use HasFactory;

    protected $table = 'monthly_depreciations';

    protected $fillable = [
        'asset_id',
        'period',
        'method',
        'monthly_expense',
        'accumulated_depreciation',
        'ending_book_value',
        'user_id',
        'generated_at',
    ];

    protected $casts = [
        'period' => 'date',
        'monthly_expense' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'ending_book_value' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    /**
     * Relation to Asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * User (Admin / Manager) who ran the depreciation
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
