<?php

namespace App\Models\Marketplace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id', 'buyer_id', 'seller_id',
        'amount', 'platform_fee', 'shipping_cost',
        'total_amount', 'escrow_amount', 'status',
        'shipping_method', 'buyer_notes', 'seller_notes',
        'paid_at', 'delivered_at', 'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'escrow_amount' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DISPUTED = 'disputed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_EXPIRED = 'expired';

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(\App\Models\User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(\App\Models\User::class, 'seller_id');
    }

    public function escrow()
    {
        return $this->morphOne(\App\Models\EscrowTransaction::class, 'order');
    }

    public function disputes()
    {
        return $this->morphMany(\App\Models\Dispute::class, 'disputable');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_id');
    }

    // Scopes
    public function scopePending($query) { return $query->where('status', self::STATUS_PENDING); }
    public function scopePaid($query) { return $query->where('status', self::STATUS_PAID); }
    public function scopeInProgress($query) { return $query->where('status', self::STATUS_IN_PROGRESS); }
    public function scopeDelivered($query) { return $query->where('status', self::STATUS_DELIVERED); }
    public function scopeCompleted($query) { return $query->where('status', self::STATUS_COMPLETED); }
    public function scopeDisputed($query) { return $query->where('status', self::STATUS_DISPUTED); }
    public function scopeByBuyer($query, int $userId) { return $query->where('buyer_id', $userId); }
    public function scopeBySeller($query, int $userId) { return $query->where('seller_id', $userId); }

    public function markAsPaid(string $transactionNo = null): string|true
    {
        if ($this->status !== self::STATUS_PENDING) {
            return 'Order is not in pending state';
        }
        $this->update(['status' => self::STATUS_PAID, 'paid_at' => now()]);
        return true;
    }

    public function markAsDelivered(): string|true
    {
        if (!in_array($this->status, [self::STATUS_PAID, self::STATUS_IN_PROGRESS])) {
            return 'Order cannot be marked as delivered in current state';
        }
        $this->update(['status' => self::STATUS_DELIVERED, 'delivered_at' => now()]);
        return true;
    }

    public function markAsCompleted(): string|true
    {
        if ($this->status !== self::STATUS_DELIVERED) {
            return 'Order must be delivered first';
        }
        $this->update(['status' => self::STATUS_COMPLETED, 'completed_at' => now()]);
        $this->listing()->update(['status' => Listing::STATUS_SOLD, 'sold_at' => now()]);
        return true;
    }

    public function markAsDisputed(): string|true
    {
        if (!in_array($this->status, [self::STATUS_PAID, self::STATUS_IN_PROGRESS, self::STATUS_DELIVERED])) {
            return 'Order cannot be disputed in current state';
        }
        $this->update(['status' => self::STATUS_DISPUTED]);
        return true;
    }

    public function markAsCancelled(): string|true
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_PAID])) {
            return 'Order cannot be cancelled after payment processing begins';
        }
        $this->update(['status' => self::STATUS_CANCELLED, 'cancelled_at' => now()]);
        return true;
    }
}