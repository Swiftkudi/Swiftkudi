<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'withdrawable_before',
        'withdrawable_after',
        'promo_credit_before',
        'promo_credit_after',
        'currency',
        'status',
        'reference_type',
        'reference_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'withdrawable_before' => 'decimal:2',
        'withdrawable_after' => 'decimal:2',
        'promo_credit_before' => 'decimal:2',
        'promo_credit_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Transaction types
     */
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_TASK_REWARD = 'task_reward';
    public const TYPE_TASK_PAYMENT = 'task_payment';
    public const TYPE_REFERRAL_BONUS = 'referral_bonus';
    public const TYPE_ACTIVATION = 'activation';
    public const TYPE_PROMO_CREDIT = 'promo_credit';
    public const TYPE_FEE = 'fee';
    public const TYPE_ESCROW_DEPOSIT = 'escrow_deposit';
    public const TYPE_ESCROW_RELEASE = 'escrow_release';
    public const TYPE_ESCROW_REFUND = 'escrow_refund';
    public const TYPE_BONUS = 'bonus';
    public const TYPE_DEDUCTION = 'deduction';

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
     * Get the model that owns this ledger entry (if reference exists)
     */
    public function reference()
    {
        return $this->morphTo('reference');
    }

    /**
     * Check if entry is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if entry is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if entry failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->amount >= 0 ? '+' : '';
        return $prefix . '₦' . number_format(abs($this->amount), 2);
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
            self::TYPE_PROMO_CREDIT => 'badge-light',
            self::TYPE_FEE => 'badge-danger',
            self::TYPE_ESCROW_DEPOSIT => 'badge-dark',
            self::TYPE_ESCROW_RELEASE => 'badge-success',
            self::TYPE_ESCROW_REFUND => 'badge-warning',
            self::TYPE_BONUS => 'badge-success',
            self::TYPE_DEDUCTION => 'badge-danger',
        ];
        
        return $types[$this->type] ?? 'badge-secondary';
    }

    /**
     * Create a ledger entry for a transaction
     */
    public static function createEntry(
        Wallet $wallet,
        string $type,
        float $amount,
        float $withdrawableBefore,
        float $withdrawableAfter,
        float $promoBefore,
        float $promoAfter,
        string $description = '',
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $metadata = []
    ): self {
        return self::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'type' => $type,
            'amount' => $amount,
            'withdrawable_before' => $withdrawableBefore,
            'withdrawable_after' => $withdrawableAfter,
            'promo_credit_before' => $promoBefore,
            'promo_credit_after' => $promoAfter,
            'currency' => 'NGN',
            'status' => 'completed',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'metadata' => $metadata,
        ]);
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
     * Scope: Recent
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: This month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
