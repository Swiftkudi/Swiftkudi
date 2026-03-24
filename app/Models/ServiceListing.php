<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'service_category_id',
        'price',
        'delivery_days',
        'revisions_included',
        'portfolio_images',
        'add_ons',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'portfolio_images' => 'array',
        'add_ons' => 'array',
        'is_featured' => 'boolean',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Get the seller (user)
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Get orders for this listing
     */
    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'listing_id');
    }

    /**
     * Get active orders
     */
    public function activeOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'listing_id')
            ->whereIn('status', ['paid', 'in_progress', 'delivered']);
    }

    /**
     * Scope for active listings
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for featured listings
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->active();
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₦' . number_format($this->price, 2);
    }

    /**
     * Get the URL slug
     */
    public function getSlugAttribute(): string
    {
        return Str::slug($this->title) . '-' . $this->id;
    }
}
