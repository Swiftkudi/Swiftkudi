<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for financial transactions
 * 
 * @property int $id
 * @property string $transaction_type
 * @property string $category
 * @property string|null $sub_category
 * @property float $amount
 * @property float|null $amount_usd
 * @property string $currency
 * @property string|null $payment_gateway
 * @property string|null $reference
 * @property int|null $user_id
 * @property int|null $created_by
 * @property string|null $description
 * @property array|null $metadata
 * @property string $status
 * @property \Carbon\Carbon $transaction_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FinancialTransaction extends Model
{
    use HasFactory;

    /**
     * Transaction types
     */
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    /**
     * Revenue categories
     */
    public const CAT_ACTIVATION = 'activation';
    public const CAT_TASK_CREATION = 'task_creation';
    public const CAT_GATEWAY_FEE = 'gateway_fee';
    public const CAT_AFFILIATE_COMMISSION = 'affiliate_commission';
    public const CAT_WITHDRAWAL_FEE = 'withdrawal_fee';
    public const CAT_ADVERTISING = 'advertising';
    public const CAT_MARKETPLACE = 'marketplace';
    public const CAT_OTHER_REVENUE = 'other_revenue';

    /**
     * Expense categories
     */
    public const CAT_GATEWAY_CHARGE = 'gateway_charge';
    public const CAT_SERVER_COST = 'server_cost';
    public const CAT_EMAIL_COST = 'email_cost';
    public const CAT_SMS_COST = 'sms_cost';
    public const CAT_STAFF_COST = 'staff_cost';
    public const CAT_REFERRAL_BONUS = 'referral_bonus';
    public const CAT_CUSTOM_EXPENSE = 'custom_expense';

    /**
     * Statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The table associated with the model.
     */
    protected $table = 'financial_transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'transaction_type',
        'category',
        'sub_category',
        'amount',
        'amount_usd',
        'currency',
        'payment_gateway',
        'reference',
        'user_id',
        'created_by',
        'description',
        'metadata',
        'status',
        'transaction_date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'amount_usd' => 'decimal:2',
        'metadata' => 'array',
        'transaction_date' => 'datetime',
    ];

    /**
     * Relationship: User associated with this transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Admin who created this transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Revenue transactions only
     */
    public function scopeRevenue($query)
    {
        return $query->where('transaction_type', self::TYPE_REVENUE);
    }

    /**
     * Scope: Expense transactions only
     */
    public function scopeExpense($query)
    {
        return $query->where('transaction_type', self::TYPE_EXPENSE);
    }

    /**
     * Scope: Completed transactions only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Filter by payment gateway
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            self::CAT_ACTIVATION => 'Activation Fee',
            self::CAT_TASK_CREATION => 'Task Creation',
            self::CAT_GATEWAY_FEE => 'Gateway Fee',
            self::CAT_AFFILIATE_COMMISSION => 'Affiliate Commission',
            self::CAT_WITHDRAWAL_FEE => 'Withdrawal Fee',
            self::CAT_ADVERTISING => 'Advertising',
            self::CAT_MARKETPLACE => 'Marketplace',
            self::CAT_OTHER_REVENUE => 'Other Revenue',
            self::CAT_GATEWAY_CHARGE => 'Gateway Charge',
            self::CAT_SERVER_COST => 'Server Cost',
            self::CAT_EMAIL_COST => 'Email Cost',
            self::CAT_SMS_COST => 'SMS Cost',
            self::CAT_STAFF_COST => 'Staff Cost',
            self::CAT_REFERRAL_BONUS => 'Referral Bonus',
            self::CAT_CUSTOM_EXPENSE => 'Custom Expense',
        ];

        return $labels[$this->category] ?? $this->category;
    }
}
