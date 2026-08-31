<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = ['asset_id', 'issue_report_id', 'title', 'maintenance_type', 'description', 'vendor', 'scheduled_at', 'completed_at', 'completion_remarks', 'cost', 'final_cost', 'status', 'created_by'];

    protected $casts = ['scheduled_at' => 'date', 'completed_at' => 'date', 'cost' => 'decimal:2', 'final_cost' => 'decimal:2'];

    public function asset() { return $this->belongsTo(Asset::class); }
    public function issueReport() { return $this->belongsTo(IssueReport::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}