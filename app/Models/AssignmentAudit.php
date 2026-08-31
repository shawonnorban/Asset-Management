<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentAudit extends Model
{
    use HasFactory;

    protected $table = 'assignment_audits';

    protected $fillable = [
        'audit_name',
        'audit_period',
        'status',
        'started_by',
        'started_at',
        'completed_by',
        'completed_at',
        'total_assignments',
        'verified_count',
        'missing_count',
        'damaged_count',
        'notes',
    ];

    protected $casts = [
        'audit_period' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status: pending, in_progress, completed
     */
    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(AssignmentAuditVerification::class, 'audit_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'in_progress' || $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getProgressPercentage(): float
    {
        if ($this->total_assignments === 0) {
            return 0;
        }
        return round(($this->verified_count / $this->total_assignments) * 100, 2);
    }
}
