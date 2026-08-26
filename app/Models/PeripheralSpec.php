<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeripheralSpec extends Model
{
    use HasFactory;

    protected $table = 'peripheral_specs';

    protected $fillable = [
        'asset_id',
        'peripheral_type',
        'connection',
        'screen_size_inch',
        'resolution',
        'panel_type',
        'refresh_rate_hz',
        'capacity_va',
        'backup_minutes',
        'note',
    ];

    protected $casts = [
        'screen_size_inch' => 'decimal:1',
    ];

    public const PERIPHERAL_TYPES = [
        'MONITOR', 'KEYBOARD', 'MOUSE', 'UPS', 'DOCKING_STATION',
        'HEADSET', 'WEBCAM', 'SCANNER', 'PROJECTOR', 'OTHER',
    ];

    public const CONNECTIONS = [
        'USB', 'HDMI', 'DISPLAYPORT', 'VGA', 'DVI', 'BLUETOOTH', 'WIRELESS', 'PS2', 'OTHER',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
