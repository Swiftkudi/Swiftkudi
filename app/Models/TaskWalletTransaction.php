<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskWalletTransaction extends Model
{
    use HasFactory;

    protected $table = 'task_wallet_transactions';

    protected $fillable = [
        'task_id',
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    // Type constants
    public const TYPE_FUND = 'fund';
    public const TYPE_PAYOUT = 'payout';
    public const TYPE_REFUND = 'refund';
    public const TYPE_COMMISSION = 'commission';

    /**
     * Get the task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskNew::class, 'task_id');
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
