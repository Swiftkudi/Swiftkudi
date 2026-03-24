<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowthListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'price',
        'delivery_days',
        'specs',
        'status',
        'rejection_reason',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'specs' => 'array',
        'is_featured' => 'boolean',
    ];

    // Type constants
    const TYPE_BACKLINKS = 'backlinks';
    const TYPE_INFLUENCER = 'influencer';
    const TYPE_NEWSLETTER = 'newsletter';
    const TYPE_LEADS = 'leads';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DELETED = 'deleted';

    /**
     * Get the seller
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get orders for this listing
     */
    public function orders(): HasMany
    {
        return $this->hasMany(GrowthOrder::class, 'listing_id');
    }

    /**
     * Check if listing is available
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get commission rate from settings
     */
    public static function getCommissionRate(): float
    {
        return (float) SystemSetting::get('growth_commission', 15); // Default 15%
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope active
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        switch($this->type) {
            case self::TYPE_BACKLINKS:
                return 'Backlinks';
            case self::TYPE_INFLUENCER:
                return 'Influencer Promotion';
            case self::TYPE_NEWSLETTER:
                return 'Newsletter';
            case self::TYPE_LEADS:
                return 'Lead Generation';
            default:
                return ucfirst($this->type);
        }
    }
}
