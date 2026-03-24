<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Events\RevenueUpdated;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction)
    {
        $this->maybeBroadcast($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction)
    {
        $this->maybeBroadcast($transaction);
    }

    /**
     * Broadcast a lightweight RevenueUpdated payload when a transaction completes.
     */
    protected function maybeBroadcast(Transaction $transaction)
    {
        try {
            if ($transaction->isCompleted()) {
                // Minimal payload so admin UI can react quickly without full aggregation
                $payload = [
                    'type' => 'transaction',
                    'transaction' => [
                        'id' => $transaction->id,
                        'amount' => (float) $transaction->amount,
                        'currency' => $transaction->currency,
                        'payment_method' => $transaction->payment_method,
                        'type' => $transaction->type,
                        'created_at' => $transaction->created_at->toDateTimeString(),
                    ],
                ];

                event(new RevenueUpdated($payload));
            }
        } catch (\Throwable $e) {
            // Never let observer failures bubble up and break the transaction flow
        }
    }
}
