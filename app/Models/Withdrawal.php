<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'fee',
        'net_amount',
        'currency',
        'method',
        'status',
        'bank_name',
        'account_number',
        'account_name',
        'usdt_address',
        'admin_notes',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Withdrawal statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Withdrawal methods
     */
    public const METHOD_BANK = 'bank';
    public const METHOD_USDT = 'usdt';

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Wallet
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Check if withdrawal is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if withdrawal is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if withdrawal was rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): bool
    {
        $this->status = self::STATUS_PROCESSING;
        $this->save();
        
        return true;
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(string $notes = null): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->processed_at = now();
        if ($notes) {
            $this->admin_notes = $notes;
        }
        $this->save();
        
        return true;
    }

    /**
     * Mark as rejected and refund
     */
    public function markAsRejected(string $notes = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->processed_at = now();
        if ($notes) {
            $this->admin_notes = $notes;
        }
        $this->save();
        
        // Refund to wallet
        $this->wallet->addWithdrawable($this->amount);
        
        return true;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₦' . number_format($this->amount, 2);
    }

    /**
     * Get formatted fee
     */
    public function getFormattedFeeAttribute(): string
    {
        return '₦' . number_format($this->fee, 2);
    }

    /**
     * Get formatted net amount
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return '₦' . number_format($this->net_amount, 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'badge-warning';
            case self::STATUS_PROCESSING:
                return 'badge-info';
            case self::STATUS_COMPLETED:
                return 'badge-success';
            case self::STATUS_REJECTED:
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get processing time estimate
     */
    public function getProcessingTimeEstimateAttribute(): string
    {
        if ($this->method === self::METHOD_USDT) {
            return '24-48 hours';
        }
        
        if ($this->status === self::STATUS_PROCESSING) {
            return 'Expected within 24 hours';
        }
        
        return '5% fee, 24-48h processing';
    }

    /**
     * Scope: Pending withdrawals
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Processing withdrawals
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope: Completed withdrawals
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Recent
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
