<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'buyer_id',
        'seller_id',
        'amount',
        'platform_fee',
        'escrow_amount',
        'status',
        'requirements',
        'delivery_notes',
        'delivered_files',
        'revision_count',
        'delivered_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'escrow_amount' => 'decimal:2',
        'delivered_files' => 'array',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DISPUTED = 'disputed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * Get the listing
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(ServiceListing::class, 'listing_id');
    }

    /**
     * Get the buyer
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get messages for this order
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ServiceMessage::class, 'order_id');
    }

    /**
     * Get reviews for this order
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ServiceReview::class, 'order_id');
    }

    /**
     * Get purchased add-ons
     */
    public function addOns(): HasMany
    {
        return $this->hasMany(ServiceOrderAddOn::class, 'order_id');
    }

    /**
     * Scope for buyer's orders
     */
    public function scopeForBuyer($query, int $userId)
    {
        return $query->where('buyer_id', $userId);
    }

    /**
     * Scope for seller's orders
     */
    public function scopeForSeller($query, int $userId)
    {
        return $query->where('seller_id', $userId);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₦' . number_format($this->amount, 2);
    }

    /**
     * Check if order can be cancelled
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING]);
    }

    /**
     * Check if order can be delivered
     */
    public function canDeliver(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Check if buyer can approve
     */
    public function canApprove(): bool
    {
        return in_array($this->status, [self::STATUS_DELIVERED]);
    }

    /**
     * Check if buyer can request revision
     */
    public function canRequestRevision(): bool
    {
        $listing = $this->listing;
        $maxRevisions = $listing ? $listing->revisions_included : 0;
        return in_array($this->status, [self::STATUS_DELIVERED]) 
            && $this->revision_count < $maxRevisions;
    }
}
