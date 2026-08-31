<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueReport extends Model
{
    use HasFactory;

    protected $table = 'issue_reports';

    protected $fillable = [
        'title',
        'description',
        'status',
        'asset_id',
        'user_id',
        'image',
        'resolution',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation: an issue report belongs to one asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Relation: an issue report is created by a user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation: an issue report can have many feedbacks
     */
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'issue_report_id');
    }
}
