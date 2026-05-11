# Fix Feature Unlock Payment Flow

## Problem
`$user->wallet_balance` is used 29 times in `OnboardingController.php` but this property does not exist on the User model or as a database column. The actual balance is at `$user->wallet->withdrawable_balance`. This causes:
- Every unlock attempt treats balance as insufficient (null < amount is always true)
- `$user->decrement('wallet_balance', $amount)` silently fails — balance never deducted
- After deposit, `completePendingFeatureUnlock` loops back to deposit forever
- Activation methods (earner, freelancer, etc.) also silently fail

## Changes

### 1. `app/Http/Controllers/OnboardingController.php`
Replace all 29 occurrences of broken wallet logic:

**Balance checks** — Replace:
```php
$user->wallet_balance
```
With:
```php
$user->wallet ? $user->wallet->withdrawable_balance : 0
```

**Balance deductions** — Replace:
```php
$user->decrement('wallet_balance', $amount);
```
With:
```php
if (!$user->wallet || !$user->wallet->deductWithdrawable($amount, 'feature_unlock', '...')) {
    throw new \RuntimeException('Failed to deduct wallet balance');
}
```

Affected methods (all lines):
- `activateEarner()` — line 189, 194
- `activateFreelancer()` — line 215, 221
- `activateDigitalProduct()` — line 244, 250
- `activateGrowth()` — line 273, 279
- `unlockEarnerFeature()` — line 370, 386, 405
- `completePendingFeatureUnlock()` — line 443, 445, 465
- `unlockBuyerFeature()` — line 551, 567, 586
- `unlockTaskCreatorFeature()` — line 648, 664, 683
- `unlockFreelancerFeature()` — line 745, 760, 778
- `unlockDigitalSellerFeature()` — line 840, 854, 871
- `unlockGrowthSellerFeature()` — line 933, 947, 964

Also fix line 1053: `getTaskCreatorFeatureExpiry` → `getBuyerFeatureExpiry` for buyer `professional_services`

### 2. `resources/views/wallet/deposit.blade.php`
Add feature unlock context alert alongside existing task creation alert:
- Show "Feature Unlock on Hold" message when `session('pending_feature_unlock')` exists
- Show the required amount to deposit

## Verification
- Test feature unlock with sufficient balance → should deduct and unlock directly
- Test feature unlock with insufficient balance → should redirect to deposit with correct amount
- After deposit → should auto-redirect back and complete the unlock
- Verify wallet balance decrements correctly (check `wallets.withdrawable_balance` in DB)
- Verify `WalletLedger` entries are created for deductions
