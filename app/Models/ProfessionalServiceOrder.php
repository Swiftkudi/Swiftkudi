<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessionalServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'buyer_id',
        'seller_id',
        'service_price',
        'addons_total',
        'total_amount',
        'platform_commission',
        'seller_payout',
        'escrow_amount',
        'paid_amount',
        'status',
        'requirements',
        'delivery_notes',
        'delivery_files',
        'revisions_used',
        'revisions_requested',
        'revision_notes',
        'delivered_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'service_price' => 'decimal:2',
        'addons_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'seller_payout' => 'decimal:2',
        'escrow_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'delivery_files' => 'array',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_REVISION = 'revision';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_DISPUTED = 'disputed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Get the service for this order
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ProfessionalService::class, 'service_id');
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
        return $this->hasMany(ProfessionalServiceMessage::class, 'order_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get reviews for this order
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProfessionalServiceReview::class, 'order_id');
    }

    /**
     * Check if buyer can request revision
     */
    public function canRequestRevision(): bool
    {
        $service = $this->service;
        $maxRevisions = $service ? $service->revisions_included : 1;
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_REVISION]) 
            && $this->revisions_used < $maxRevisions;
    }

    /**
     * Check if order can be cancelled
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PAID]);
    }

    /**
     * Check if order can be completed
     */
    public function canComplete(): bool
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_REVISION]);
    }

    /**
     * Scope to orders for a buyer
     */
    public function scopeForBuyer($query, int $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    /**
     * Scope to orders for a seller
     */
    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    /**
     * Scope by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
