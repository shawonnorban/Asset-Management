<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $table = 'asset_assignments';

    protected $fillable = [
        'asset_id',
        'employee_id',
        'location_id',
        'assigned_at',
        'returned_at',
        'condition_on_assign',
        'condition_on_return',
        'note',
        'handled_by',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'returned_at' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    /** IT staff who handed the asset over or took it back. */
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isOpen(): bool
    {
        return $this->returned_at === null;
    }
}
