<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'asset_code',
        'image',
        'asset_name',
        'brand',
        'model',
        'serial_number',
        'description',
        'added_date',
        'vendor',
        'invoice_no',
        'purchase_date',
        'purchase_cost',
        'warranty_start',
        'warranty_end',
        'status',
        'condition',
        'category_id',
        'location_id',
        'employee_id',
        'parent_asset_id',
    ];

    protected $casts = [
        'added_date' => 'date',
        'purchase_date' => 'date',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    /**
     * RELATIONS
     */

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    /** The employee currently holding this asset. */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** The computer this peripheral is attached to. */
    public function parentAsset()
    {
        return $this->belongsTo(Asset::class, 'parent_asset_id');
    }

    /** Peripherals attached to this computer. */
    public function childAssets()
    {
        return $this->hasMany(Asset::class, 'parent_asset_id');
    }

    public function depreciationSetting()
    {
        return $this->hasOne(AssetDepreciationSetting::class, 'asset_id');
    }

    public function monthlyDepreciations()
    {
        return $this->hasMany(MonthlyDepreciation::class, 'asset_id');
    }

    /**
     * SPECIFICATIONS
     * Only the one matching the category's asset_type is ever filled in.
     */

    public function computerSpec()
    {
        return $this->hasOne(ComputerSpec::class, 'asset_id');
    }

    public function peripheralSpec()
    {
        return $this->hasOne(PeripheralSpec::class, 'asset_id');
    }

    public function printerSpec()
    {
        return $this->hasOne(PrinterSpec::class, 'asset_id');
    }

    public function networkDeviceSpec()
    {
        return $this->hasOne(NetworkDeviceSpec::class, 'asset_id');
    }

    /**
     * ASSIGNMENT HISTORY
     */

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class, 'asset_id');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'asset_id');
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class, 'asset_id');
    }

    public function transfers()
    {
        return $this->hasMany(AssetTransfer::class, 'asset_id');
    }

    public function disposals()
    {
        return $this->hasMany(AssetDisposal::class, 'asset_id');
    }

    public function lifecycleLogs()
    {
        return $this->hasMany(AssetLifecycleLog::class, 'asset_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'metadata->asset_id');
    }

    /** The assignment that has not been returned yet, if any. */
    public function currentAssignment()
    {
        return $this->hasOne(AssetAssignment::class, 'asset_id')
            ->whereNull('returned_at')
            ->latestOfMany();
    }

    public function licenseAssignments()
    {
        return $this->hasMany(SoftwareLicenseAssignment::class, 'asset_id');
    }

    public function softwareLicenses()
    {
        return $this->belongsToMany(
            SoftwareLicense::class,
            'software_license_assignments',
            'asset_id',
            'software_license_id'
        )->withPivot(['installed_at', 'removed_at', 'note'])->withTimestamps();
    }

    /**
     * HELPERS
     */

    /** COMPUTER / PERIPHERAL / PRINTER / NETWORK_DEVICE / OTHER */
    public function getAssetTypeAttribute(): string
    {
        return optional($this->category)->asset_type ?? 'OTHER';
    }

    /** The filled-in spec row for this asset, whichever type it is. */
    public function spec()
    {
        return match ($this->asset_type) {
            'COMPUTER'       => $this->computerSpec,
            'PERIPHERAL'     => $this->peripheralSpec,
            'PRINTER'        => $this->printerSpec,
            'NETWORK_DEVICE' => $this->networkDeviceSpec,
            default          => null,
        };
    }

    public function isUnderWarranty(): bool
    {
        return $this->warranty_end !== null && $this->warranty_end->isFuture();
    }
}
