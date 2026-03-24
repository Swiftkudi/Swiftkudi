<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for expense logs
 * 
 * @property int $id
 * @property string $expense_type
 * @property string $description
 * @property float $amount
 * @property string $currency
 * @property string|null $payment_gateway
 * @property int|null $created_by
 * @property \Carbon\Carbon $expense_date
 * @property string $status
 * @property string|null $notes
 * @property string|null $attachment_url
 * @property string|null $recurring_type
 * @property bool $is_recurring
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExpenseLog extends Model
{
    use HasFactory;

    /**
     * Expense types
     */
    public const TYPE_GATEWAY_FEE = 'gateway_fee';
    public const TYPE_SERVER_COST = 'server_cost';
    public const TYPE_EMAIL_COST = 'email_cost';
    public const TYPE_SMS_COST = 'sms_cost';
    public const TYPE_STAFF_COST = 'staff_cost';
    public const TYPE_REFERRAL_BONUS = 'referral_bonus';
    public const TYPE_MARKETING = 'marketing';
    public const TYPE_OPERATIONS = 'operations';
    public const TYPE_CUSTOM = 'custom';

    /**
     * Statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Recurring types
     */
    public const RECURRING_DAILY = 'daily';
    public const RECURRING_WEEKLY = 'weekly';
    public const RECURRING_MONTHLY = 'monthly';
    public const RECURRING_YEARLY = 'yearly';

    /**
     * The table associated with the model.
     */
    protected $table = 'expense_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'expense_type',
        'description',
        'amount',
        'currency',
        'payment_gateway',
        'created_by',
        'expense_date',
        'status',
        'notes',
        'attachment_url',
        'recurring_type',
        'is_recurring',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Relationship: Admin who created this expense
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Approved expenses only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: Filter by expense type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('expense_type', $type);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Get expense type label
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_GATEWAY_FEE => 'Payment Gateway Fee',
            self::TYPE_SERVER_COST => 'Server/Hosting Cost',
            self::TYPE_EMAIL_COST => 'Email Service Cost',
            self::TYPE_SMS_COST => 'SMS Service Cost',
            self::TYPE_STAFF_COST => 'Staff/Operations Cost',
            self::TYPE_REFERRAL_BONUS => 'Referral Bonus Paid',
            self::TYPE_MARKETING => 'Marketing Cost',
            self::TYPE_OPERATIONS => 'Operations Cost',
            self::TYPE_CUSTOM => 'Custom Expense',
        ];

        return $labels[$this->expense_type] ?? $this->expense_type;
    }
}
