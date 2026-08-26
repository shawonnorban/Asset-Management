<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareLicense extends Model
{
    use HasFactory;

    protected $table = 'software_licenses';

    protected $fillable = [
        'name',
        'publisher',
        'version',
        'license_type',
        'license_key',
        'seats_total',
        'vendor',
        'invoice_no',
        'purchase_date',
        'purchase_cost',
        'expiry_date',
        'note',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    public const LICENSE_TYPES = ['PERPETUAL', 'SUBSCRIPTION', 'OEM', 'VOLUME', 'OPEN_SOURCE'];

    public function assignments()
    {
        return $this->hasMany(SoftwareLicenseAssignment::class, 'software_license_id');
    }

    /** Assignments that have not been removed yet. */
    public function activeAssignments()
    {
        return $this->hasMany(SoftwareLicenseAssignment::class, 'software_license_id')
            ->whereNull('removed_at');
    }

    public function assets()
    {
        return $this->belongsToMany(
            Asset::class,
            'software_license_assignments',
            'software_license_id',
            'asset_id'
        )->withPivot(['installed_at', 'removed_at', 'note'])->withTimestamps();
    }

    public function getSeatsUsedAttribute(): int
    {
        return $this->activeAssignments()->count();
    }

    public function getSeatsAvailableAttribute(): int
    {
        return max(0, $this->seats_total - $this->seats_used);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}
