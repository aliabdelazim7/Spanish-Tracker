<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'billing_cycle',
        'currency',
        'amount_paid',
        'payment_method',
        'payment_reference',
        'current_period_starts_at',
        'current_period_ends_at',
        'canceled_at',
    ];

    protected $casts = [
        'amount_paid' => 'float',
        'current_period_starts_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function isActive(): bool
    {
        if (!in_array($this->status, ['active', 'trialing'])) {
            return false;
        }

        if ($this->current_period_ends_at === null) {
            return true; // Lifetime or free
        }

        return $this->current_period_ends_at->isFuture();
    }

    public function isPro(): bool
    {
        return $this->isActive() && $this->plan && $this->plan->slug !== 'free';
    }

    public function daysRemaining(): int
    {
        if ($this->current_period_ends_at === null) {
            return 9999;
        }

        return max(0, (int) Carbon::now()->diffInDays($this->current_period_ends_at, false));
    }
}
