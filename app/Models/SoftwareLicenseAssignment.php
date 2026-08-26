<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareLicenseAssignment extends Model
{
    use HasFactory;

    protected $table = 'software_license_assignments';

    protected $fillable = [
        'software_license_id',
        'asset_id',
        'installed_at',
        'removed_at',
        'note',
        'handled_by',
    ];

    protected $casts = [
        'installed_at' => 'date',
        'removed_at' => 'date',
    ];

    public function license()
    {
        return $this->belongsTo(SoftwareLicense::class, 'software_license_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
