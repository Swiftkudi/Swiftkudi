<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeatureUnlockManager
{
    private const PERMITTED_PERIODS = ['initial', 'monthly', 'quarterly'];

    public function getFeatureConfig(string $feature): ?array
    {
        $feature = $this->normalizeFeatureKey($feature);
        return config("features.features.{$feature}");
    }

    public function normalizeFeatureKey(string $feature): string
    {
        $aliases = config('features.feature_aliases', []);
        return $aliases[$feature] ?? $feature;
    }

    public function getFeatureCost(string $feature, string $period): array
    {
        $config = $this->getFeatureConfig($feature);

        if (!$config || empty($config['periods'][$period])) {
            throw new \InvalidArgumentException("Invalid feature or period: {$feature} / {$period}");
        }

        return $config['periods'][$period];
    }

    public function buildPendingUnlock(User $user, string $feature, string $period, string $accountType, string $redirectRoute): array
    {
        $feature = $this->normalizeFeatureKey($feature);
        $costData = $this->getFeatureCost($feature, $period);
        $amount = $costData['cost'];
        $months = $costData['months'];
        $key = $this->getPendingUnlockKey($user, $feature, $period, $accountType);

        return [
            'idempotency_key' => $key,
            'account_type' => $accountType,
            'feature' => $feature,
            'period' => $period,
            'amount' => $amount,
            'months' => $months,
            'redirect_route' => $redirectRoute,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    public function getPendingUnlockKey(User $user, string $feature, string $period, string $accountType): string
    {
        return sprintf('%s_%s_%s_%s_%s', $accountType, $feature, $period, $user->id, now()->timestamp);
    }

    public function hasRecentUnlockTransaction(User $user, string $feature, string $accountType): bool
    {
        $referencePrefix = sprintf('%s_feature_%s_%s', $accountType, $feature, $user->id);
        return \App\Models\FinancialTransaction::where('user_id', $user->id)
            ->where('reference', 'like', $referencePrefix . '%')
            ->where('created_at', '>', now()->subMinutes(5))
            ->exists();
    }

    public function processFeatureUnlock(User $user, string $feature, string $period, string $accountType): array
    {
        $feature = $this->normalizeFeatureKey($feature);
        $costData = $this->getFeatureCost($feature, $period);
        $amount = $costData['cost'];
        $months = $costData['months'];
        $wallet = $user->wallet;
        $balance = $wallet ? $wallet->withdrawable_balance : 0;

        if (!$wallet) {
            return [
                'status' => 'no_wallet',
                'message' => 'Wallet not available.',
            ];
        }

        $idempotencyKey = $this->getPendingUnlockKey($user, $feature, $period, $accountType);
        $processedKey = 'feature_unlock_processed_' . md5($idempotencyKey);

        // Check database for existing processed request (durable idempotency)
        $existingKey = \App\Models\IdempotencyKey::where('key', $processedKey)
            ->where('user_id', $user->id)
            ->first();
        
        if ($existingKey && $existingKey->response_status === 'success') {
            return [
                'status' => 'duplicate',
                'message' => 'This feature has already been unlocked.',
            ];
        }

        if (Cache::has($processedKey) || $this->hasRecentUnlockTransaction($user, $feature, $accountType)) {
            return [
                'status' => 'duplicate',
                'message' => 'This feature unlock is already being processed.',
            ];
        }

        if ($balance < $amount) {
            return [
                'status' => 'insufficient_funds',
                'amount' => $amount,
                'required_amount' => $amount - $balance,
            ];
        }

        Cache::put($processedKey, true, now()->addMinutes(5));

        try {
            DB::transaction(function () use ($user, $feature, $amount, $months, $accountType, $idempotencyKey, $processedKey) {
                $user->wallet->deductWithdrawable($amount, 'feature_unlock', 'Feature unlock deduction');
                $user->unlockFeature($feature, $months);

                \App\Models\FinancialTransaction::create([
                    'user_id' => $user->id,
                    'transaction_type' => 'expense',
                    'category' => 'feature_unlock',
                    'amount' => $amount,
                    'description' => ucfirst(str_replace('_', ' ', $accountType)) . ' feature unlock: ' . $feature,
                    'reference' => $accountType . '_feature_' . $feature . '_' . $user->id . '_' . time(),
                ]);
            });

            // Store idempotency record for future durability
            \App\Models\IdempotencyKey::updateOrCreate(
                ['key' => $processedKey],
                [
                    'user_id' => $user->id,
                    'entity_type' => 'feature_unlock',
                    'entity_id' => $user->id,
                    'method' => 'POST',
                    'request_hash' => [
                        'feature' => $feature,
                        'period' => $period,
                        'account_type' => $accountType,
                        'idempotency_key' => $idempotencyKey,
                    ],
                    'response_status' => 'success',
                    'response_body' => [
                        'status' => 'success',
                        'feature' => $feature,
                        'months' => $months,
                    ],
                    'expires_at' => now()->addDays(30),
                ]
            );

            Cache::forget($processedKey);

            return [
                'status' => 'success',
                'message' => ucfirst(str_replace('_', ' ', $feature)) . ' unlocked for ' . $months . ' months.',
            ];
        } catch (\Exception $e) {
            Cache::forget($processedKey);
            throw $e;
        }
    }

    public function buildUnlockResponse(User $user, string $feature, string $period, string $accountType, string $redirectRoute): array
    {
        $feature = $this->normalizeFeatureKey($feature);
        $costData = $this->getFeatureCost($feature, $period);
        $amount = $costData['cost'];
        $months = $costData['months'];
        $balance = $user->wallet ? $user->wallet->withdrawable_balance : 0;

        $pendingData = $this->buildPendingUnlock($user, $feature, $period, $accountType, $redirectRoute);

        if ($balance >= $amount) {
            return [
                'status' => 'ready',
                'pending' => $pendingData,
                'amount' => $amount,
                'months' => $months,
                'balance' => $balance,
            ];
        }

        return [
            'status' => 'insufficient_funds',
            'pending' => $pendingData,
            'amount' => $amount,
            'months' => $months,
            'balance' => $balance,
            'required_amount' => $amount - $balance,
        ];
    }

    public function processPendingUnlock(User $user, array $pendingUnlock): array
    {
        if (empty($pendingUnlock['feature']) || empty($pendingUnlock['account_type']) || empty($pendingUnlock['period'])) {
            return [
                'status' => 'invalid_pending',
                'message' => 'Pending unlock data is invalid.',
            ];
        }

        $result = $this->processFeatureUnlock(
            $user,
            $pendingUnlock['feature'],
            $pendingUnlock['period'],
            $pendingUnlock['account_type']
        );

        if ($result['status'] === 'success' && !empty($pendingUnlock['redirect_route'])) {
            $result['redirect_route'] = $pendingUnlock['redirect_route'];
        }

        return $result;
    }
}
