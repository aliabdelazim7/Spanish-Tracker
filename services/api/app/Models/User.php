<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function lessonAttempts()
    {
        return $this->hasMany(LessonAttempt::class);
    }

    public function placementAttempts()
    {
        return $this->hasMany(PlacementAttempt::class);
    }

    public function userVocabulary()
    {
        return $this->hasMany(UserVocabulary::class);
    }

    public function storyProgress()
    {
        return $this->hasMany(StoryProgress::class);
    }

    public function xpTransactions()
    {
        return $this->hasMany(XpTransaction::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')->withPivot('unlocked_at');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
