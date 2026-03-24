<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'max_tasks',
        'max_services',
        'max_listings',
        'featured_included',
        'featured_limit',
        'priority_support',
        'analytics',
        'is_active',
        'is_popular',
        'icon',
        'color',
        'position',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'featured_included' => 'boolean',
        'priority_support' => 'boolean',
        'analytics' => 'boolean',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('position');
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function getYearlySavingsPercentAttribute(): int
    {
        if (!$this->monthly_price || !$this->yearly_price) {
            return 0;
        }
        
        $yearlyMonthly = $this->monthly_price * 12;
        $savings = $yearlyMonthly - $this->yearly_price;
        return round(($savings / $yearlyMonthly) * 100);
    }
}
