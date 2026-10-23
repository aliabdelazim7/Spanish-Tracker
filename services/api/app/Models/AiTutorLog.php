<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTutorLog extends Model
{
    protected $fillable = [
        'user_id',
        'action_type',
        'prompt_summary',
        'response_summary',
        'model_used',
        'tokens_used',
        'status',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
