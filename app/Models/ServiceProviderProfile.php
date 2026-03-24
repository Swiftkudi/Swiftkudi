<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProviderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_available',
        'hourly_rate',
        'bio',
        'skills',
        'portfolio_links',
        'certifications',
        'total_orders_completed',
        'average_rating',
        'total_reviews',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'skills' => 'array',
        'portfolio_links' => 'array',
        'certifications' => 'array',
        'is_available' => 'boolean',
        'average_rating' => 'decimal:2',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get services by this provider
     */
    public function services(): HasMany
    {
        return $this->hasMany(ProfessionalService::class, 'user_id', 'user_id');
    }

    /**
     * Get active services count
     */
    public function activeServicesCount(): int
    {
        return $this->services()->where('status', ProfessionalService::STATUS_ACTIVE)->count();
    }

    /**
     * Scope to only available providers
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to filter by skill
     */
    public function scopeWithSkill($query, string $skill)
    {
        return $query->where('skills', 'like', "%{$skill}%");
    }

    /**
     * Scope to order by rating
     */
    public function scopeTopRated($query)
    {
        return $query->orderBy('average_rating', 'desc');
    }
}
