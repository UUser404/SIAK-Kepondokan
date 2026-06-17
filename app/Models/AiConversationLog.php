<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiConversationLog extends Model
{
    use HasFactory;

    protected $table = 'ai_conversation_logs';

    protected $fillable = [
        'user_id',
        'session_id',
        'question',
        'answer',
        'model_used',
        'response_time_ms',
        'guard_applied',
        'guard_issues',
    ];

    protected function casts(): array
    {
        return [
            'guard_applied'    => 'boolean',
            'guard_issues'     => 'array',
            'response_time_ms' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
