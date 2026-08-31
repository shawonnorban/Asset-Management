<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    use HasFactory;

    protected $table = 'asset_transfers';

    protected $fillable = [
        'asset_id',
        'from_location_id',
        'to_location_id',
        'from_employee_id',
        'to_employee_id',
        'requested_by',
        'approved_by',
        'status',
        'reason',
        'notes',
        'requested_at',
        'transferred_at',
    ];

    protected $casts = [
        'requested_at' => 'date',
        'transferred_at' => 'date',
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

    public function fromLocation()
    {
        return $this->belongsTo(AssetLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(AssetLocation::class, 'to_location_id');
    }

    public function fromEmployee()
    {
        return $this->belongsTo(Employee::class, 'from_employee_id');
    }

    public function toEmployee()
    {
        return $this->belongsTo(Employee::class, 'to_employee_id');
    }
}
