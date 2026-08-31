<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDisposal extends Model
{
    use HasFactory;

    protected $table = 'asset_disposals';

    protected $fillable = [
        'asset_id',
        'requested_by',
        'approved_by',
        'status',
        'reason',
        'method',
        'value_recovered',
        'requested_at',
        'disposed_at',
        'notes',
    ];

    protected $casts = [
        'requested_at' => 'date',
        'disposed_at' => 'date',
        'value_recovered' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
