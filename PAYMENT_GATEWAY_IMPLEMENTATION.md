# Production Payment Gateway Implementation

## Overview
Complete production-ready payment flow has been wired for Paystack, Kora, and Stripe payment gateways.

## Components Implemented

### 1. PaymentController (`app/Http/Controllers/PaymentController.php`)
Handles all payment lifecycle operations:

**Methods:**
- `initialize(Request)` - Initiates payment, redirects to gateway
- `callback(Request)` - Handles post-payment verification with idempotency
- `paystackWebhook()` - Verifies Paystack webhook signatures and processes payments
- `koraWebhook()` - Verifies Kora webhook signatures and processes payments  
- `stripeWebhook()` - Verifies Stripe webhook with HMAC or SDK
- `processWebhookPayment()` - Idempotent webhook payment processor with transaction locking

**Key Features:**
- Transaction reference generation (`DEP_<unique>`)
- Pending transaction creation before payment
- Currency conversion (multi-currency support)
- Idempotency checks to prevent duplicate credits
- Database transactions with row-level locking for webhooks
- Wallet ledger entries for audit trail
- Revenue aggregation integration

### 2. PaymentGatewayService Updates (`app/Services/PaymentGatewayService.php`)
Enhanced to load credentials from SystemSetting (admin-configured) with config fallback:

**Key Changes:**
- Constructor accepts optional `$gateway` and `$mode` parameters
- Auto-detects active gateway from SystemSetting: `paystack_enabled`, `kora_enabled`, `stripe_enabled`
- Credentials loaded from:
  1. SystemSetting (encrypted secret keys via `getDecrypted()`)
  2. Config fallback (env-based via `config/services.php`)
- Priority: Paystack → Kora → Stripe

**Existing Methods Used:**
- `initializePayment(User, amount, currency, description)` - Returns array with `authorization_url` and `reference`
- `verifyPayment(reference)` - Returns verification data with `status`, `amount_in_ngn`
- `convertToNgn()` - Multi-currency conversion

### 3. Routes (`routes/web.php`)
Added authenticated and public payment routes:

```php
// Authenticated payment routes
Route::prefix('payment')->name('payment.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/initialize', [PaymentController::class, 'initialize'])->name('initialize');
    Route::get('/callback', [PaymentController::class, 'callback'])->name('callback');
});

// Public webhook routes (signature-verified)
Route::post('/webhooks/paystack', [PaymentController::class, 'paystackWebhook'])->name('webhooks.paystack');
Route::post('/webhooks/kora', [PaymentController::class, 'koraWebhook'])->name('webhooks.kora');
Route::post('/webhooks/stripe', [PaymentController::class, 'stripeWebhook'])->name('webhooks.stripe');
```

### 4. WalletController Integration (`app/Http/Controllers/WalletController.php`)
Modified `deposit()` method to redirect to payment gateway instead of demo deposit:

**Before:**
- Created demo transaction immediately
- No actual payment processing

**After:**
- Validates amount (min 100 NGN)
- Stores session context for post-payment redirect
- Redirects to `payment.initialize` with amount and currency
- Removed demo deposit logic

### 5. Config Services (`config/services.php`)
Added payment gateway configuration:

```php
'paystack' => [
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'enabled' => env('PAYSTACK_ENABLED', false),
],
'kora' => [
    'public_key' => env('KORA_PUBLIC_KEY'),
    'secret_key' => env('KORA_SECRET_KEY'),
    'enabled' => env('KORA_ENABLED', false),
],
'stripe' => [
    'public_key' => env('STRIPE_PUBLIC_KEY'),
    'secret_key' => env('STRIPE_SECRET_KEY'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'enabled' => env('STRIPE_ENABLED', false),
],
```

## Payment Flow

### User Initiates Deposit
1. User navigates to `/wallet/deposit`
2. Fills amount and submits form
3. POST to `/wallet/deposit` (WalletController)
4. Validation occurs (min 100 NGN)
5. Redirects to `GET /payment/initialize?amount=X&currency=NGN`

### Payment Initialization
1. `PaymentController::initialize()` validates request
2. Creates **pending** Transaction record with reference `DEP_<unique>`
3. Calls `PaymentGatewayService::initializePayment()`
4. Gateway service generates authorization URL
5. Redirects user to payment gateway (Paystack/Kora/Stripe)
6. User completes payment on gateway

### Payment Callback (Redirect)
1. Gateway redirects user back to `/payment/callback?reference=<ref>`
2. `PaymentController::callback()` processes:
   - Finds transaction by reference
   - Checks idempotency (if already `completed`, exits early)
   - Calls `PaymentGatewayService::verifyPayment()` for verification
   - If verified: DB transaction with wallet credit, ledger entry, transaction status update
   - Stores redirect context from session if applicable (e.g., task creation flow)
   - Returns success message

### Webhook Verification (Async)
Each gateway sends webhook with signature header:
- **Paystack**: `x-paystack-signature` - HMAC-SHA512
- **Kora**: `x-kora-signature` - HMAC-SHA256
- **Stripe**: `stripe-signature` - HMAC-SHA256 (SDK or manual verification)

Flow:
1. Webhook received at `/webhooks/<gateway>`
2. Signature verified
3. `charge.success` / `payment_intent.succeeded` event processed
4. `processWebhookPayment()` called with row-level lock (`lockForUpdate()`)
5. Idempotency check prevents duplicate processing
6. Transaction marked as `completed`, wallet credited

## Idempotency & Security

### Idempotency
- **Transaction Reference**: Unique per deposit attempt (`DEP_<uniqid>`)
- **Status Checks**: Skip processing if status already `completed`
- **Row Locking**: Webhooks use `lockForUpdate()` for simultaneous webhook handling
- **Reference-Based**: Both callback and webhooks use transaction reference lookup

### Security
- **Signature Verification**: All webhooks verified by signature before processing
- **Amount Validation**: Gateway response amounts compared with stored transaction
- **User Association**: Transactions tied to authenticated user
- **DB Transactions**: Payment processing wrapped in atomic DB transaction
- **Encrypted Keys**: SystemSetting stores secret keys encrypted

## Database Changes

### Transaction Model (Used)
- `reference`: Unique per payment (e.g., `DEP_604a7b2e1c43a`)
- `status`: `pending` → `completed` or `failed`
- `metadata`: JSON storing gateway reference, verification response, webhook data
- `completed_at`: Timestamp when payment verified

### WalletLedger (Created)
One entry per deposit:
- `type`: `credit`
- `category`: `deposit`
- `amount`: Amount in NGN
- `balance_after`: Wallet balance after credit

## Testing

### Local Testing (Sandbox)
1. Configure `.env`:
   ```
   PAYSTACK_PUBLIC_KEY=<sandbox_key>
   PAYSTACK_SECRET_KEY=<sandbox_secret>
   PAYSTACK_ENABLED=true
   ```

2. Navigate to `/wallet/deposit`
3. Enter amount (min 100)
4. System redirects to Paystack sandbox payment page
5. Complete test payment
6. Redirected back to `/payment/callback`
7. Wallet updated with deposit

### Webhook Testing
Use Stripe CLI or Paystack dashboard to send test webhooks:

```bash
# Stripe
stripe listen --forward-to localhost:8000/webhooks/stripe
stripe trigger charge.succeeded

# Paystack (use dashboard charge logs to trigger callback)
```

## Related Settings (Admin Panel)
Administrators can configure gateways via SystemSetting:
- `paystack_public_key` (visible)
- `paystack_secret_key` (encrypted)
- `paystack_enabled`
- `kora_public_key`, `kora_secret_key`, `kora_enabled`
- `stripe_public_key`, `stripe_secret_key`, `stripe_enabled`

Settings controller (SettingsController::testGateway) tests credentials before saving.

## Error Handling
- Payment initialization failures → logged + user error message
- Verification failures → transaction marked as failed
- Webhook processing errors → logged + retry-safe (idempotent)
- Missing transaction → logged warning, user redirected to wallet

## Next Steps for Production

1. **Install Stripe SDK** (optional): `composer require stripe/stripe-php`
2. **Configure Production Keys**: Update `.env` with live gateway credentials
3. **Test Full Flow**: Deposit → Payment → Callback → Wallet Credit
4. **Monitor Logs**: Check `storage/logs/laravel.log` for payment processing
5. **Webhook Registration**: Register webhook URLs with each gateway
6. **Currency Rates**: Ensure `PaymentGatewayService::loadRates()` has accurate exchange rates
7. **Notifications**: Add email notifications after successful deposit (optional)

## Files Modified
- ✅ `app/Http/Controllers/PaymentController.php` (created)
- ✅ `app/Http/Controllers/WalletController.php` (modified deposit method)
- ✅ `app/Services/PaymentGatewayService.php` (updated credential loading)
- ✅ `routes/web.php` (added payment routes)
- ✅ `config/services.php` (added gateway config sections)
- ✅ `composer.json` (no changes needed)
