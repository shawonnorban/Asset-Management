<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentAuditVerification extends Model
{
    use HasFactory;

    protected $table = 'assignment_audit_verifications';

    protected $fillable = [
        'audit_id',
        'assignment_id',
        'asset_id',
        'employee_id',
        'verification_status',
        'condition_observed',
        'verified_by',
        'verified_at',
        'remarks',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Verification Status: confirmed, missing, lost, damaged, returned, transferred
     */
    public function audit(): BelongsTo
    {
        return $this->belongsTo(AssignmentAudit::class, 'audit_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class, 'assignment_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isIssue(): bool
    {
        return in_array($this->verification_status, ['missing', 'lost', 'damaged']);
    }
}
