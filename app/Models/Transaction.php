<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'currency',
        'exchange_rate',
        'payment_method',
        'status',
        'reference',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
        'metadata' => 'array',
    ];

    /**
     * Transaction types
     */
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_TASK_REWARD = 'task_reward';
    public const TYPE_TASK_PAYMENT = 'task_payment';
    public const TYPE_TASK_EARNING = 'task_earning';
    public const TYPE_REFERRAL_BONUS = 'referral_bonus';
    public const TYPE_ACTIVATION = 'activation';
    public const TYPE_FEE = 'fee';
    public const TYPE_REFUND = 'refund';
    public const TYPE_BONUS = 'bonus';

    /**
     * Relationship: Wallet
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        $this->save();
        
        return true;
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(): bool
    {
        $this->status = 'failed';
        $this->save();
        
        return true;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->amount >= 0 ? '+' : '';
        return $prefix . $this->currency . ' ' . number_format(abs($this->amount), 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        switch ($this->status) {
            case 'pending':
                return 'badge-warning';
            case 'completed':
                return 'badge-success';
            case 'failed':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClassAttribute(): string
    {
        $types = [
            self::TYPE_DEPOSIT => 'badge-success',
            self::TYPE_WITHDRAWAL => 'badge-warning',
            self::TYPE_TASK_REWARD => 'badge-info',
            self::TYPE_TASK_PAYMENT => 'badge-primary',
            self::TYPE_REFERRAL_BONUS => 'badge-success',
            self::TYPE_ACTIVATION => 'badge-secondary',
            self::TYPE_FEE => 'badge-danger',
            self::TYPE_REFUND => 'badge-warning',
            self::TYPE_BONUS => 'badge-success',
        ];
        
        return $types[$this->type] ?? 'badge-secondary';
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Recent
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Generate unique reference
     */
    public static function generateReference(string $type): string
    {
        return strtoupper($type) . '-' . time() . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
