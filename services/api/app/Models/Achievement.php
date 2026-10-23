<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'code',
        'title_ar',
        'description_ar',
        'icon',
        'badge_category',
        'xp_reward',
        'threshold',
    ];

    protected $casts = [
        'xp_reward' => 'integer',
        'threshold' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')->withPivot('unlocked_at');
    }
}
