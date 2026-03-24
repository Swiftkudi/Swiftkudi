# Payment Gateway Request/Response Flow

## 1. Deposit Form Submission

### Request
```
POST /wallet/deposit
Content-Type: application/x-www-form-urlencoded
Authorization: Bearer <user-token>

amount=5000&currency=NGN
```

### WalletController::deposit() Processing
- Validates: `amount >= 100`
- Stores session: `payment_success_redirect` (if set)
- Redirects to payment initialization

### Response
```
303 See Other
Location: /payment/initialize?amount=5000&currency=NGN
```

---

## 2. Payment Initialization

### Request
```
GET /payment/initialize?amount=5000&currency=NGN
Authorization: Bearer <user-token>
```

### PaymentController::initialize() Processing

#### Step 1: Validation & Transaction Creation
```php
Transaction::create([
    'user_id' => auth()->id(),
    'type' => 'deposit',
    'amount' => 5000,
    'currency' => 'NGN',
    'status' => 'pending',
    'reference' => 'DEP_604a7b2e1c43a',
    'metadata' => json_encode([...])
]);
```

#### Step 2: Gateway Service Initialization
```php
$result = PaymentGatewayService->initializePayment(
    user: User,
    amount: 5000,
    currency: 'NGN',
    description: 'Wallet deposit via payment gateway'
);
```

Returns:
```php
// For Paystack
[
    'success' => true,
    'authorization_url' => 'https://checkout.paystack.com/...',
    'reference' => 'DEP_604a7b2e1c43a'
]

// For Kora or Stripe (similar structure)
```

#### Step 3: Redirect to Payment Gateway
```php
redirect()->away($result['authorization_url']);
```

### Response
```
302 Found
Location: https://checkout.paystack.com/...
```

---

## 3. Payment Gateway Checkout

### User Actions (on Paystack/Kora/Stripe)
1. Enters card details
2. Completes payment
3. Gateway redirects back to callback URL

### Gateway Redirect
```
GET /payment/callback?reference=DEP_604a7b2e1c43a
```

---

## 4. Payment Callback (Post-Payment)

### Request
```
GET /payment/callback?reference=DEP_604a7b2e1c43a
Authorization: Bearer <user-token>
```

### PaymentController::callback() Processing

#### Step 1: Find Transaction
```php
$transaction = Transaction::where('reference', 'DEP_604a7b2e1c43a')
    ->where('type', 'deposit')
    ->first();
// Status: pending, Amount: 5000 NGN
```

#### Step 2: Idempotency Check
```php
if ($transaction->status === 'completed') {
    // Return: "Payment already processed"
}
```

#### Step 3: Gateway Verification
```php
$verification = PaymentGatewayService->verifyPayment('DEP_604a7b2e1c43a');

// Returns:
[
    'status' => 'success',
    'amount' => 5000,
    'amount_in_ngn' => 5000,
    'gateway' => 'paystack',
    'timestamp' => '2024-01-15T10:30:00Z'
]
```

#### Step 4: Database Transaction (Atomic)
```php
DB::transaction(function () {
    // 1. Get/Create Wallet
    $wallet = $user->wallet ?? Wallet::create([...]);
    
    // 2. Add Credit
    $wallet->addWithdrawable(5000, 'Payment deposit verified');
    // wallet_balance: 5000
    
    // 3. Update Transaction
    Transaction::update([
        'status' => 'completed',
        'completed_at' => now(),
        'metadata' => json_encode([
            'verified_at' => now()->toIso8601String(),
            'gateway_response' => $verification
        ])
    ]);
    
    // 4. Create Ledger Entry
    WalletLedger::create([
        'wallet_id' => $wallet->id,
        'transaction_id' => $transaction->id,
        'type' => 'credit',
        'category' => 'deposit',
        'amount' => 5000,
        'balance_after' => 5000,
        'description' => 'Deposit via payment gateway'
    ]);
});
```

#### Step 5: Redirect with Success
```php
// Check for post-payment redirect (e.g., task creation)
$redirect = session('payment_success_redirect') ?? route('wallet.index');
return redirect($redirect)->with('success', '....');
```

### Response
```
303 See Other
Location: /wallet
X-Message: "Payment successful! Your wallet has been credited."
```

### Final State in Database
```
transactions table:
- id: 123
- reference: DEP_604a7b2e1c43a
- user_id: 45
- type: deposit
- amount: 5000
- status: completed
- completed_at: 2024-01-15 10:30:15
- metadata: {verified_at: "2024-01-15T10:30:15Z", gateway_response: {...}}

wallets table:
- user_id: 45
- withdrawable_balance: 5000 (increased from 0)

wallet_ledgers table:
- wallet_id: 23
- transaction_id: 123
- type: credit
- category: deposit
- amount: 5000
- balance_after: 5000
```

---

## 5. Webhook Verification (Async)

### Paystack Webhook

#### Request
```
POST /webhooks/paystack
Content-Type: application/json
X-Paystack-Signature: sha512=abc123def456...

{
  "event": "charge.success",
  "data": {
    "id": 987654,
    "reference": "DEP_604a7b2e1c43a",
    "amount": 500000,
    "status": "success",
    "metadata": {...}
  }
}
```

#### Signature Verification
```php
$body = request()->getContent();
$signature = request()->header('x-paystack-signature');
$secret = config('services.paystack.secret_key');

// Verify: hash_hmac('sha512', $body, $secret) === $signature
```

#### Processing (Idempotent)
```php
$transaction = Transaction::where('reference', 'DEP_604a7b2e1c43a')->first();

// If status already 'completed':
Log::info('Webhook payment already processed');
return response()->json(['status' => 'success']);

// Otherwise, process same as callback (with lockForUpdate() for concurrency)
DB::transaction(function () {
    $transaction = Transaction::lockForUpdate()->find($id);
    // ... same credit logic ...
});
```

#### Response
```
200 OK
Content-Type: application/json

{"status": "success"}
```

### Kora Webhook
Similar structure but:
- Header: `X-Kora-Signature`
- Event: `charge.success`
- HMAC: SHA256

### Stripe Webhook
Different structure:
```
POST /webhooks/stripe
Content-Type: application/json
Stripe-Signature: t=<timestamp>,v1=<signature>

{
  "type": "payment_intent.succeeded",
  "data": {
    "object": {
      "id": "pi_1Abc123",
      "amount": 500000,
      "currency": "usd",
      "metadata": {"reference": "DEP_604a7b2e1c43a"}
    }
  }
}
```

---

## 6. Error Scenarios

### Missing Gateway Credentials
```
POST /payment/initialize
→ PaymentGatewayService->initializePayment()
→ throws Exception("Secret key not configured")
→ catch: Log error
→ return with('error', 'Payment initialization failed: ...')
```

### Payment Verification Failed
```
GET /payment/callback?reference=DEP_...
→ Verify returns status != 'success'
→ Transaction marked as 'failed'
→ Log warning
→ return with('error', 'Payment verification failed')
```

### Duplicate Webhook Processing
```
// First webhook received and processed
Transaction status: completed

// Duplicate webhook sent
→ Process begins
→ lockForUpdate() acquired
→ Status check: completed
→ Early return (no duplicate credit)
→ return 200 OK
```

### Transaction Not Found
```
GET /payment/callback?reference=INVALID
→ Transaction::where('reference', 'INVALID')->first() → null
→ return with('error', 'Transaction not found')
```

---

## 7. Multi-Currency Example

### Deposit in USD
```
POST /wallet/deposit
amount=10&currency=USD
```

### Initialization Flow
```php
// PaymentGatewayService
$amountInNgn = convertToNgn(10, 'USD');
// Uses rate: 1 USD = 1500 NGN
// Result: 15000 NGN

Transaction::create([
    'amount' => 10,
    'currency' => 'USD',
    ...
]);

// Initialize with converted amount
POST to gateway with amount=1500000 (in kobo)
```

### Verification
```php
$verificationData = verifyPayment('DEP_...');
// Returns: amount_in_ngn: 15000

// Credit wallet in NGN
$wallet->addWithdrawable(15000, 'Deposit in USD');
```

### Ledger Record
```
WalletLedger::create([
    'amount' => 15000,  // Stored in NGN
    'description' => 'Deposit in USD (rate: 1500)'
]);
```

---

## 8. Status Code Reference

### PaymentController Methods

| Method | Status Codes | Notes |
|--------|-------------|-------|
| `initialize()` | 302/303 | Redirect to gateway or error |
| `callback()` | 303 | Redirect to wallet with message |
| `paystackWebhook()` | 200/401 | 200 if processed, 401 if invalid signature |
| `koraWebhook()` | 200/401 | Same as Paystack |
| `stripeWebhook()` | 200/401 | With SDK or manual verification |

### Transaction Statuses
- `pending` - Created on initialization, awaiting payment
- `completed` - Successfully verified and wallet credited
- `failed` - Verification failed or error occurred

---

## 9. Key Response Objects

### PaymentGatewayService::initializePayment() Returns
```php
[
    'success' => bool,
    'authorization_url' => string (URL to gateway checkout),
    'reference' => string (unique deposit reference),
    'message' => string (error message if !success)
]
```

### PaymentGatewayService::verifyPayment() Returns
```php
[
    'status' => 'success'|'failed',
    'amount' => float (original amount paid),
    'amount_in_ngn' => float (converted to NGN),
    'currency' => string (original currency),
    'gateway' => string (paystack|kora|stripe),
    'timestamp' => string (ISO8601),
    ...gateway_specific_fields...
]
```

---

## 10. Testing Checklist

- [ ] Test deposit form rejects amount < 100
- [ ] Test initialization redirects to payment gateway
- [ ] Test callback with valid reference credits wallet
- [ ] Test callback with invalid reference shows error
- [ ] Test duplicate callback (same reference) doesn't double-credit
- [ ] Test failed payment marks transaction as failed
- [ ] Test webhook with invalid signature is rejected
- [ ] Test webhook with valid signature processes payment
- [ ] Test multi-currency conversion (USD → NGN)
- [ ] Test transaction reference format (DEP_xxxx)
- [ ] Test idempotency across concurrent requests
- [ ] Test wallet ledger creates correct entry
- [ ] Test post-payment redirect (task creation flow)
