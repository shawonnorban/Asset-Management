<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackReply extends Model
{
    use HasFactory;

    protected $table = 'feedback_replies';

    protected $fillable = [
        'feedback_reply',
        'feedback_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation: reply belongs to one feedback
     */
    public function feedback()
    {
        return $this->belongsTo(Feedback::class, 'feedback_id');
    }

    /**
     * Relation: reply is created by a user (nullable)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
