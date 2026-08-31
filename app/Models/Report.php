<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'asset_id',
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'created_date',
        'completed_date',
    ];

    protected $casts = [
        'created_date' => 'datetime',
        'completed_date' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
