<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $table = 'maintenance_requests';

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'title',
        'description',
        'priority',
        'status',
        'requested_at',
        'scheduled_at',
        'completed_at',
        'estimated_cost',
        'actual_cost',
        'vendor_name',
        'requested_by',
        'assigned_to',
    ];

    protected $casts = [
        'requested_at' => 'date',
        'scheduled_at' => 'date',
        'completed_at' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
