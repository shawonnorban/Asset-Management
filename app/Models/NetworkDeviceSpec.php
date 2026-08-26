<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkDeviceSpec extends Model
{
    use HasFactory;

    protected $table = 'network_device_specs';

    protected $fillable = [
        'asset_id',
        'device_role',
        'hostname',
        'ip_address',
        'mac_address',
        'ip_type',
        'subnet_mask',
        'gateway',
        'vlan',
        'port_count',
        'port_speed',
        'is_managed',
        'supports_poe',
        'wifi_standard',
        'firmware_version',
        'management_url',
        'rack_position',
        'note',
    ];

    protected $casts = [
        'is_managed' => 'boolean',
        'supports_poe' => 'boolean',
    ];

    public const DEVICE_ROLES = [
        'ROUTER', 'SWITCH', 'ACCESS_POINT', 'FIREWALL', 'MODEM', 'NAS', 'SERVER', 'OTHER',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
