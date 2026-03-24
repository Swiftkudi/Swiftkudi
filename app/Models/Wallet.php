<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'withdrawable_balance',
        'promo_credit_balance',
        'total_earned',
        'total_spent',
        'pending_balance',
        'escrow_balance',
        'is_activated',
        'activated_at',
        'currency',
        // Earning categories (optional - may not exist in all environments)
        'total_task_earnings',
        'total_referral_bonuses',
        'total_deposits',
        'total_fees',
    ];

    protected $casts = [
        'withdrawable_balance' => 'decimal:2',
        'promo_credit_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'escrow_balance' => 'decimal:2',
        'is_activated' => 'boolean',
        'activated_at' => 'datetime',
        // Earning categories (optional)
        'total_task_earnings' => 'decimal:2',
        'total_referral_bonuses' => 'decimal:2',
        'total_deposits' => 'decimal:2',
        'total_fees' => 'decimal:2',
    ];
    
    /**
     * Check if earning categories columns exist
     */
    protected static function hasEarningCategories(): bool
    {
        static $hasColumns = null;
        if ($hasColumns === null) {
            $hasColumns = Schema::hasColumn('wallets', 'total_task_earnings');
        }
        return $hasColumns;
    }
    
    /**
     * Override the getAttribute method to handle missing columns
     */
    public function getAttribute($key)
    {
        // Return 0 for earning category columns if they don't exist
        $earningCategories = ['total_task_earnings', 'total_referral_bonuses', 'total_deposits', 'total_fees'];
        if (in_array($key, $earningCategories) && !self::hasEarningCategories()) {
            return 0;
        }
        
        return parent::getAttribute($key);
    }

    /**
     * Currencies
     */
    public const CURRENCY_NGN = 'NGN';
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_USDT = 'USDT';

    /**
     * Earnings split ratios
     */
    public const WITHDRAWABLE_RATIO = 0.80; // 80%
    public const PROMO_CREDIT_RATIO = 0.20; // 20%

    /**
     * Withdrawal fees
     */
    public const FEE_STANDARD = 0.05; // 5%
    public const FEE_INSTANT = 0.10; // 10%

    /**
     * Minimum withdrawal amounts
     */
    public const MIN_WITHDRAWAL_NGN = 3000;
    public const MIN_WITHDRAWAL_USD = 50;
    public const MIN_WITHDRAWAL_USDT = 50;

    /**
     * Relationship: User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relationship: Ledger entries
     */
    public function ledger()
    {
        return $this->hasMany(WalletLedger::class);
    }

    /**
     * Get total balance (withdrawable + promo credit)
     */
    public function getTotalBalanceAttribute(): float
    {
        return $this->withdrawable_balance + $this->promo_credit_balance;
    }

    /**
     * Get formatted total balance
     */
    public function getFormattedTotalBalanceAttribute(): string
    {
        return $this->formatCurrency($this->total_balance);
    }

    /**
     * Format amount with currency symbol
     */
    public function formatCurrency(float $amount): string
    {
        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($amount, 2);
    }

    /**
     * Get currency symbol
     */
    public function getCurrencySymbol(): string
    {
        if ($this->currency == self::CURRENCY_USD || $this->currency == self::CURRENCY_USDT) {
            return '$';
        }
        if ($this->currency == self::CURRENCY_NGN) {
            return '₦';
        }
        return '₦';
    }

    /**
     * Check if user can afford amount from withdrawable balance
     */
    public function canWithdraw(float $amount): bool
    {
        return $this->withdrawable_balance >= $amount;
    }

    /**
     * Check if user can afford amount from promo credit
     */
    public function canAffordFromPromo(float $amount): bool
    {
        return $this->promo_credit_balance >= $amount;
    }

    /**
     * Check if user can afford total amount
     */
    public function canAffordTotal(float $amount): bool
    {
        return $this->total_balance >= $amount;
    }

    /**
     * Add funds to withdrawable balance
     */
    public function addWithdrawable(float $amount, string $type, ?string $description = null): bool
    {
        if ($amount <= 0) {
            return false;
        }
        
        $before = $this->withdrawable_balance;
        $this->withdrawable_balance += $amount;
        $this->total_earned += $amount;
        
        // Track earnings by category
        if ($type === 'task_earning' || $type === 'task_reward') {
            $this->total_task_earnings = ($this->total_task_earnings ?? 0) + $amount;
        } elseif ($type === 'referral_bonus') {
            $this->total_referral_bonuses = ($this->total_referral_bonuses ?? 0) + $amount;
        } elseif ($type === 'deposit') {
            $this->total_deposits = ($this->total_deposits ?? 0) + $amount;
        }
        
        $this->save();
        
        // Record ledger entry using the static method for consistency
        WalletLedger::createEntry(
            $this,
            $type,
            $amount,
            $before,
            $this->withdrawable_balance,
            $this->promo_credit_balance,
            $this->promo_credit_balance,
            $description ?? 'Wallet deposit',
            $type,
            null,
            []
        );
        
        return true;
    }

    /**
     * Add funds to promo credit balance
     */
    public function addPromoCredit(float $amount, string $type, ?string $description = null): bool
    {
        if ($amount <= 0) {
            return false;
        }
        
        $before = $this->promo_credit_balance;
        $this->promo_credit_balance += $amount;
        $this->total_earned += $amount;
        $this->save();
        
        // Record ledger entry using the static method for consistency
        WalletLedger::createEntry(
            $this,
            $type,
            $amount,
            $this->withdrawable_balance,
            $this->withdrawable_balance,
            $before,
            $this->promo_credit_balance,
            $description ?? 'Promo credit',
            $type,
            null,
            ['is_promo' => true]
        );
        
        return true;
    }

    /**
     * Deduct from withdrawable balance
     */
    public function deductWithdrawable(float $amount, string $type, ?string $description = null): bool
    {
        if ($amount <= 0 || !$this->canWithdraw($amount)) {
            return false;
        }
        
        $before = $this->withdrawable_balance;
        $this->withdrawable_balance -= $amount;
        $this->total_spent += $amount;
        $this->save();
        
        // Record ledger entry using the static method for consistency
        WalletLedger::createEntry(
            $this,
            $type,
            -$amount,
            $before,
            $this->withdrawable_balance,
            $this->promo_credit_balance,
            $this->promo_credit_balance,
            $description ?? 'Deduction',
            $type,
            null,
            []
        );
        
        return true;
    }

    /**
     * Deduct from promo credit balance
     */
    public function deductPromoCredit(float $amount, string $type, ?string $description = null): bool
    {
        if ($amount <= 0 || !$this->canAffordFromPromo($amount)) {
            return false;
        }
        
        $before = $this->promo_credit_balance;
        $this->promo_credit_balance -= $amount;
        $this->total_spent += $amount;
        $this->save();
        
        // Record ledger entry using the static method for consistency
        WalletLedger::createEntry(
            $this,
            $type,
            -$amount,
            $this->withdrawable_balance,
            $this->withdrawable_balance,
            $before,
            $this->promo_credit_balance,
            $description ?? 'Promo deduction',
            $type,
            null,
            ['is_promo' => true]
        );
        
        return true;
    }

    /**
     * Split earnings (80% withdrawable, 20% promo credit)
     */
    public function addEarnings(float $totalAmount, float $promoCreditAmount = null): array
    {
        if ($totalAmount <= 0) {
            return ['withdrawable' => 0, 'promo_credit' => 0];
        }
        
        // Use provided promo credit amount or calculate from ratio
        if ($promoCreditAmount === null) {
            $promoCreditAmount = round($totalAmount * self::PROMO_CREDIT_RATIO, 2);
        }
        
        $withdrawable = round($totalAmount - $promoCreditAmount, 2);
        
        $this->addWithdrawable($withdrawable, 'task_earning');
        $this->addPromoCredit($promoCreditAmount, 'task_earning');
        
        return compact('withdrawable', 'promoCreditAmount');
    }

    /**
     * Add funds to escrow (for task creation)
     */
    public function addToEscrow(float $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }
        
        // First try to use promo credit
        if ($this->promo_credit_balance >= $amount) {
            $this->deductPromoCredit($amount, 'escrow');
        } else {
            // Use remaining promo credit
            $promoUsed = $this->promo_credit_balance;
            $this->promo_credit_balance = 0;
            
            // Use withdrawable for remaining
            $remaining = $amount - $promoUsed;
            if ($this->deductWithdrawable($remaining, 'escrow')) {
                // Record promo credit usage (already handled by deductPromoCredit)
            } else {
                return false;
            }
        }
        
        $before = $this->escrow_balance;
        $this->escrow_balance += $amount;
        $this->save();
        
        // Record escrow entry using the static method for consistency
        WalletLedger::createEntry(
            $this,
            WalletLedger::TYPE_ESCROW_DEPOSIT,
            $amount,
            $this->withdrawable_balance,
            $this->withdrawable_balance,
            $this->promo_credit_balance,
            $this->promo_credit_balance,
            'Escrow deposit',
            'escrow',
            null,
            ['escrow_before' => $before, 'escrow_after' => $this->escrow_balance]
        );

        return true;
    }

    /**
     * Release from escrow to worker
     */
    public function releaseFromEscrow(float $amount, ?Wallet $recipient = null): bool
    {
        if ($amount <= 0 || $this->escrow_balance < $amount) {
            return false;
        }
        
        $before = $this->escrow_balance;
        $this->escrow_balance -= $amount;
        $this->save();
        // If a recipient wallet is provided, credit them. Otherwise credit this wallet (legacy behavior).
        if ($recipient && $recipient->exists) {
            $recipient->addEarnings($amount);
        } else {
            $this->addEarnings($amount);
        }
        
        // Record release using the static method for consistency
        WalletLedger::createEntry(
            $this,
            WalletLedger::TYPE_ESCROW_RELEASE,
            $amount,
            $this->withdrawable_balance - $amount,
            $this->withdrawable_balance,
            $this->promo_credit_balance,
            $this->promo_credit_balance,
            'Escrow release',
            'escrow',
            null,
            ['escrow_before' => $before, 'escrow_after' => $this->escrow_balance]
        );
        
        return true;
    }

    /**
     * Refund from escrow to wallet
     */
    public function refundFromEscrow(float $amount): bool
    {
        if ($amount <= 0 || $this->escrow_balance < $amount) {
            return false;
        }
        
        $before = $this->escrow_balance;
        $this->escrow_balance -= $amount;
        $this->addWithdrawable($amount, 'escrow_refund');
        
        // Record refund using the static method for consistency
        WalletLedger::createEntry(
            $this,
            WalletLedger::TYPE_ESCROW_REFUND,
            $amount,
            $this->withdrawable_balance - $amount,
            $this->withdrawable_balance,
            $this->promo_credit_balance,
            $this->promo_credit_balance,
            'Escrow refund',
            'escrow',
            null,
            ['escrow_before' => $before, 'escrow_after' => $this->escrow_balance]
        );
        
        return true;
    }

    /**
     * Activate wallet
     */
    public function activate(): bool
    {
        $this->is_activated = true;
        $this->activated_at = now();
        $this->save();
        
        return true;
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalance(): array
    {
        return [
            'withdrawable' => $this->formatCurrency($this->withdrawable_balance),
            'promo_credit' => $this->formatCurrency($this->promo_credit_balance),
            'total' => $this->formatCurrency($this->total_balance),
            'escrow' => $this->formatCurrency($this->escrow_balance),
        ];
    }

    /**
     * Check if minimum withdrawal requirement is met
     */
    public function meetsMinimumWithdrawal(): bool
    {
        return $this->withdrawable_balance >= $this->getMinimumWithdrawal();
    }

    /**
     * Get minimum withdrawal amount
     */
    public function getMinimumWithdrawal(): float
    {
        if ($this->currency == self::CURRENCY_USD || $this->currency == self::CURRENCY_USDT) {
            return self::MIN_WITHDRAWAL_USD;
        }
        if ($this->currency == self::CURRENCY_NGN) {
            return self::MIN_WITHDRAWAL_NGN;
        }
        return self::MIN_WITHDRAWAL_NGN;
    }

    /**
     * Calculate withdrawal fee
     */
    public function calculateWithdrawalFee(float $amount, bool $instant = false): float
    {
        if ($instant) {
            return round($amount * self::FEE_INSTANT, 2);
        }
        
        return round($amount * self::FEE_STANDARD, 2);
    }

    /**
     * Get withdrawal fee percentage
     */
    public function getFeePercentage(bool $instant = false): string
    {
        return ($instant ? self::FEE_INSTANT : self::FEE_STANDARD) * 100 . '%';
    }

    /**
     * Process withdrawal
     */
    public function processWithdrawal(float $amount, bool $instant = false, string $method = Withdrawal::METHOD_BANK): array
    {
        $fee = $this->calculateWithdrawalFee($amount, $instant);
        $netAmount = $amount - $fee;
        
        $withdrawalType = $instant ? 'instant_withdrawal' : 'withdrawal';
        if (!$this->deductWithdrawable($amount, $withdrawalType)) {
            return [
                'success' => false,
                'message' => 'Insufficient withdrawable balance',
            ];
        }
        
        // Create withdrawal record
        $withdrawal = Withdrawal::create([
            'user_id' => $this->user_id,
            'wallet_id' => $this->id,
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'currency' => $this->currency,
            'method' => $method,
            'is_instant' => $instant,
            'status' => $instant ? 'processing' : 'pending',
        ]);
        
        return [
            'success' => true,
            'message' => 'Withdrawal request submitted successfully.',
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'instant' => $instant,
            'withdrawal_id' => $withdrawal->id,
            'formatted' => [
                'amount' => $this->formatCurrency($amount),
                'fee' => $this->formatCurrency($fee),
                'net' => $this->formatCurrency($netAmount),
            ],
        ];
    }

    /**
     * Convert currency
     */
    public function convertCurrency(string $toCurrency, float $rate): bool
    {
        if ($toCurrency == $this->currency) {
            return false;
        }
        
        $amount = $this->total_balance;
        $convertedAmount = $amount * $rate;
        
        $withdrawableBefore = $this->withdrawable_balance;
        $promoBefore = $this->promo_credit_balance;
        
        $this->withdrawable_balance = $convertedAmount;
        $this->promo_credit_balance = 0;
        $this->currency = $toCurrency;
        $this->save();
        
        // Record conversion using the static method for consistency
        WalletLedger::createEntry(
            $this,
            'currency_conversion',
            $convertedAmount,
            $withdrawableBefore,
            $this->withdrawable_balance,
            $promoBefore,
            0,
            'Currency conversion from ' . $this->currency . ' to ' . $toCurrency,
            'conversion',
            null,
            ['from_currency' => $this->currency, 'to_currency' => $toCurrency, 'rate' => $rate]
        );
        
        return true;
    }

    /**
     * Scope: Activated wallets
     */
    public function scopeActivated($query)
    {
        return $query->where('is_activated', true);
    }

    /**
     * Scope: By currency
     */
    public function scopeByCurrency($query, string $currency)
    {
        return $query->where('currency', $currency);
    }
}
