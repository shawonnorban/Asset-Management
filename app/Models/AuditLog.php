<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    /**
     * occurred_at is used instead of the default created_at
     */
    public $timestamps = false;

    /**
     * Mass assignable columns
     */
    protected $fillable = [
        'occurred_at',
        'user_id',
        'user_name',
        'action',
        'table_name',
        'row_id',
        'message',
        'before_data',
        'after_data',
        'url',
        'ip_address',
        'http_method',
        'created_at',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'occurred_at' => 'datetime',
        'before_data' => 'array',
        'after_data'  => 'array',
        'created_at'  => 'datetime',
    ];

    /**
     * User who performed the action
     * Nullable (in case the user was deleted)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
