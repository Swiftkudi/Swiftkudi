<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for activation logs
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $referrer_id
 * @property float $activation_fee
 * @property float $referral_bonus
 * @property float $platform_revenue
 * @property string|null $payment_method
 * @property string|null $payment_gateway
 * @property string|null $reference
 * @property string $status
 * @property string $activation_type
 * @property \Carbon\Carbon $activated_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ActivationLog extends Model
{
    use HasFactory;

    /**
     * Activation types
     */
    public const TYPE_NORMAL = 'normal';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_PROMO = 'promo';

    /**
     * Statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    /**
     * The table associated with the model.
     */
    protected $table = 'activation_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'referrer_id',
        'activation_fee',
        'referral_bonus',
        'platform_revenue',
        'payment_method',
        'payment_gateway',
        'reference',
        'status',
        'activation_type',
        'activated_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'activation_fee' => 'decimal:2',
        'referral_bonus' => 'decimal:2',
        'platform_revenue' => 'decimal:2',
        'activated_at' => 'datetime',
    ];

    /**
     * Relationship: User who activated
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Referrer (who referred this user)
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Scope: Completed activations only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Normal activations
     */
    public function scopeNormal($query)
    {
        return $query->where('activation_type', self::TYPE_NORMAL);
    }

    /**
     * Scope: Referral activations
     */
    public function scopeReferral($query)
    {
        return $query->where('activation_type', self::TYPE_REFERRAL);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('activated_at', [$startDate, $endDate]);
    }

    /**
     * Get activation type label
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_NORMAL => 'Normal Activation',
            self::TYPE_REFERRAL => 'Referral Activation',
            self::TYPE_PROMO => 'Promo Activation',
        ];

        return $labels[$this->activation_type] ?? $this->activation_type;
    }

    /**
     * Calculate totals for a given period
     */
    public static function getTotals($startDate, $endDate): array
    {
        $activations = self::completed()->dateRange($startDate, $endDate);

        $totalActivations = $activations->count();
        $normalActivations = $activations->normal()->count();
        $referralActivations = $activations->referral()->count();

        $totalRevenue = $activations->sum('platform_revenue');
        $totalReferralBonus = $activations->sum('referral_bonus');

        return [
            'total_activations' => $totalActivations,
            'normal_activations' => $normalActivations,
            'referral_activations' => $referralActivations,
            'total_revenue' => $totalRevenue,
            'total_referral_bonus' => $totalReferralBonus,
            'net_activation_profit' => $totalRevenue - $totalReferralBonus,
        ];
    }
}
