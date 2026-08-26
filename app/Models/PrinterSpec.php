<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrinterSpec extends Model
{
    use HasFactory;

    protected $table = 'printer_specs';

    protected $fillable = [
        'asset_id',
        'printer_type',
        'is_color',
        'is_multifunction',
        'supports_duplex',
        'max_paper_size',
        'toner_model',
        'drum_model',
        'monthly_duty_cycle',
        'connection',
        'hostname',
        'ip_address',
        'mac_address',
        'ip_type',
        'management_url',
        'note',
    ];

    protected $casts = [
        'is_color' => 'boolean',
        'is_multifunction' => 'boolean',
        'supports_duplex' => 'boolean',
    ];

    public const PRINTER_TYPES = ['LASER', 'INKJET', 'DOT_MATRIX', 'THERMAL', 'PLOTTER'];
    public const CONNECTIONS = ['USB', 'ETHERNET', 'WIFI', 'SHARED'];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
