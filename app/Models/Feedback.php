<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'decision_analysis',
        'issue_report_id',
        'status',
        'asset_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation: feedback belongs to one issue report
     */
    public function issueReport()
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }

    /**
     * Relation: feedback belongs to one asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Relation: feedback is created by a user (nullable)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation: feedback can have many replies
     */
    public function replies()
    {
        return $this->hasMany(FeedbackReply::class, 'feedback_id');
    }
}
