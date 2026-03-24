<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'billing_cycle',
        'amount_paid',
        'started_at',
        'expires_at',
        'cancelled_at',
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->save();
    }

    public function extend(int $days): void
    {
        if ($this->expires_at && $this->expires_at->isFuture()) {
            $this->expires_at = $this->expires_at->addDays($days);
        } else {
            $this->expires_at = now()->addDays($days);
        }
        $this->save();
    }
}
