<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'price_egp',
        'price_usd',
        'billing_cycle',
        'features_ar',
        'features_en',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price_egp' => 'float',
        'price_usd' => 'float',
        'features_ar' => 'array',
        'features_en' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    public function isFree(): bool
    {
        return $this->slug === 'free' || $this->price_egp == 0;
    }
}
