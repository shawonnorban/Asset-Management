<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComputerSpec extends Model
{
    use HasFactory;

    protected $table = 'computer_specs';

    protected $fillable = [
        'asset_id',
        'form_factor',
        'cpu',
        'cpu_cores',
        'gpu',
        'motherboard',
        'psu',
        'ram_gb',
        'ram_type',
        'storage_type',
        'storage_gb',
        'secondary_storage_type',
        'secondary_storage_gb',
        'hostname',
        'domain',
        'mac_address',
        'ip_address',
        'ip_type',
        'os',
        'os_version',
        'os_license_key',
        'office_license_key',
        'antivirus',
        'note',
    ];

    public const FORM_FACTORS = ['DESKTOP', 'LAPTOP', 'ALL_IN_ONE', 'WORKSTATION', 'SERVER'];
    public const STORAGE_TYPES = ['HDD', 'SSD', 'NVME', 'HYBRID'];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /** e.g. "512 GB NVME + 1000 GB HDD" */
    public function getStorageSummaryAttribute(): string
    {
        $parts = [];

        if ($this->storage_gb) {
            $parts[] = trim($this->storage_gb . ' GB ' . $this->storage_type);
        }
        if ($this->secondary_storage_gb) {
            $parts[] = trim($this->secondary_storage_gb . ' GB ' . $this->secondary_storage_type);
        }

        return $parts ? implode(' + ', $parts) : '-';
    }
}
