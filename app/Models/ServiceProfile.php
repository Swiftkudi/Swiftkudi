<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_available',
        'offers_services',
        'headline',
        'bio',
        'hourly_rate',
        'skills',
        'portfolio_links',
        'rating_average',
        'review_count',
        'order_completed',
        'last_active_at',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'offers_services' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'skills' => 'array',
        'portfolio_links' => 'array',
        'rating_average' => 'decimal:2',
        'review_count' => 'integer',
        'order_completed' => 'integer',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope for available profiles
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('offers_services', true);
    }

    /**
     * Scope by skill
     */
    public function scopeWithSkill($query, string $skill)
    {
        return $query->whereJsonContains('skills', $skill);
    }

    /**
     * Get formatted hourly rate
     */
    public function getFormattedHourlyRateAttribute(): string
    {
        return $this->hourly_rate ? '₦' . number_format($this->hourly_rate, 2) . '/hr' : null;
    }
}
