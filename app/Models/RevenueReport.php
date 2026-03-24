<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'currency',
        'gateway',
        'gross_amount',
        'gateway_fees',
        'refunds',
        'worker_payouts',
        'commissions_paid',
        'taxes',
        'platform_net',
        'transaction_count',
        'task_amount',
        'total_deposits',
        'pending_withdrawals',
        'total_transactions_amount',
        'total_wallet_balance',
        'total_withdrawable_balance',
        'total_withdrawn',
        'admin_deposits',
        'activation_fees',
        'commission_fees',
        'meta',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:4',
        'gateway_fees' => 'decimal:4',
        'refunds' => 'decimal:4',
        'worker_payouts' => 'decimal:4',
        'commissions_paid' => 'decimal:4',
        'taxes' => 'decimal:4',
        'platform_net' => 'decimal:4',
        'transaction_count' => 'integer',
        'task_amount' => 'decimal:4',
        'total_deposits' => 'decimal:4',
        'pending_withdrawals' => 'decimal:4',
        'total_transactions_amount' => 'decimal:4',
        'total_wallet_balance' => 'decimal:4',
        'total_withdrawable_balance' => 'decimal:4',
        'total_withdrawn' => 'decimal:4',
        'admin_deposits' => 'decimal:4',
        'activation_fees' => 'decimal:4',
        'commission_fees' => 'decimal:4',
        'meta' => 'array',
        'date' => 'date',
    ];
}
