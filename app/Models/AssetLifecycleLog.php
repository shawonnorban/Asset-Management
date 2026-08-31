<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLifecycleLog extends Model
{
    use HasFactory;

    protected $table = 'asset_lifecycle_logs';

    protected $fillable = [
        'asset_id',
        'user_id',
        'event_type',
        'description',
        'old_values',
        'new_values',
        'event_at',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
