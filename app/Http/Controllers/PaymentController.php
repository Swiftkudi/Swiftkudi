<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Services\PaymentGatewayService;
use App\Services\RevenueAggregator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentGatewayService;
    protected $revenueAggregator;

    public function __construct(PaymentGatewayService $paymentGatewayService, RevenueAggregator $revenueAggregator)
    {
        $this->paymentGatewayService = $paymentGatewayService;
        $this->revenueAggregator = $revenueAggregator;
    }

    /**
     * Initialize payment and redirect to gateway
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|in:NGN,USD,GBP,EUR',
        ]);

        $user = Auth::user();
        $amount = $request->input('amount');
        $currency = $request->input('currency', 'NGN');

        // Generate unique reference
        $reference = 'DEP_' . strtoupper(uniqid());

        // Create pending transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'reference' => $reference,
            'description' => 'Wallet deposit via payment gateway',
            'metadata' => json_encode([
                'payment_method' => 'gateway',
                'initiated_at' => now()->toIso8601String(),
            ]),
        ]);

        try {
            // Initialize payment through gateway service (old signature: User, amount, currency, description)
            $result = $this->paymentGatewayService->initializePayment(
                $user,
                $amount,
                $currency,
                'Wallet deposit via payment gateway'
            );

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Payment initialization failed');
            }

            // Update transaction with gateway reference
            $transaction->update([
                'reference' => $result['reference'],
                'metadata' => json_encode(array_merge(
                    json_decode($transaction->metadata, true),
                    ['gateway_reference' => $result['reference']]
                )),
            ]);

            // Redirect to payment authorization URL
            return redirect()->away($result['authorization_url']);

        } catch (\Exception $e) {
            Log::error('Payment initialization failed', [
                'user_id' => $user->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            $transaction->update(['status' => 'failed']);

            return back()->with('error', 'Payment initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment callback from gateway
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->input('reference');

        if (!$reference) {
            Log::warning('Payment callback received without reference', ['query' => $request->all()]);
            return redirect()->route('wallet.index')->with('error', 'Invalid payment reference.');
        }

        try {
            // Find transaction by reference
            $transaction = Transaction::where('reference', $reference)
                ->where('type', 'deposit')
                ->first();

            if (!$transaction) {
                Log::warning('Transaction not found for reference', ['reference' => $reference]);
                return redirect()->route('wallet.index')->with('error', 'Transaction not found.');
            }

            // Check if already processed (idempotency)
            if ($transaction->status === 'completed') {
                return redirect()->route('wallet.index')->with('info', 'Payment already processed.');
            }

            // Verify payment with gateway
            $verificationData = $this->paymentGatewayService->verifyPayment($reference);

            if ($verificationData['status'] === 'success') {
                DB::transaction(function () use ($transaction, $verificationData) {
                    $user = $transaction->user;
                    $wallet = $user->wallet;

                    if (!$wallet) {
                        $wallet = Wallet::create([
                            'user_id' => $user->id,
                            'withdrawable_balance' => 0,
                            'promo_credit_balance' => 0,
                            'total_earned' => 0,
                            'total_spent' => 0,
                            'pending_balance' => 0,
                            'escrow_balance' => 0,
                        ]);
                    }

                    // Convert amount to NGN if needed
                    $amountInNGN = $verificationData['amount_in_ngn'] ?? $transaction->amount;

                    // Credit wallet
                    $wallet->addWithdrawable($amountInNGN, 'Payment deposit verified');

                    // Update transaction
                    $transaction->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'metadata' => json_encode(array_merge(
                            json_decode($transaction->metadata, true) ?? [],
                            [
                                'verified_at' => now()->toIso8601String(),
                                'gateway_response' => $verificationData,
                            ]
                        )),
                    ]);

                    // Create wallet ledger entry
                    WalletLedger::create([
                        'wallet_id' => $wallet->id,
                        'transaction_id' => $transaction->id,
                        'type' => 'credit',
                        'category' => 'deposit',
                        'amount' => $amountInNGN,
                        'balance_after' => $wallet->withdrawable_balance,
                        'description' => 'Deposit via payment gateway',
                    ]);

                    // Revenue will be aggregated automatically by RevenueAggregator job

                    Log::info('Payment verified and credited', [
                        'user_id' => $user->id,
                        'transaction_id' => $transaction->id,
                        'reference' => $transaction->reference,
                        'amount' => $amountInNGN,
                    ]);
                });

                // Check for redirect after payment (e.g., task creation flow)
                $redirectRoute = session('payment_success_redirect');
                if ($redirectRoute) {
                    session()->forget('payment_success_redirect');
                    return redirect($redirectRoute)->with('success', '💰 Payment successful! Your wallet has been credited.');
                }

                return redirect()->route('wallet.index')->with('success', 'Payment successful! Your wallet has been credited.');

            } else {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => json_encode(array_merge(
                        json_decode($transaction->metadata, true) ?? [],
                        ['verification_failed' => $verificationData]
                    )),
                ]);

                Log::warning('Payment verification failed', [
                    'reference' => $reference,
                    'response' => $verificationData,
                ]);

                return redirect()->route('wallet.index')->with('error', 'Payment verification failed.');
            }

        } catch (\Exception $e) {
            Log::error('Payment callback processing failed', [
                'reference' => $reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($transaction)) {
                $transaction->update(['status' => 'failed']);
            }

            return redirect()->route('wallet.index')->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle Paystack webhook
     */
    public function paystackWebhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('x-paystack-signature');
        $body = $request->getContent();
        $secretKey = config('services.paystack.secret_key');

        if (!$signature || hash_hmac('sha512', $body, $secretKey) !== $signature) {
            Log::warning('Invalid Paystack webhook signature', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('Paystack webhook received', ['event' => $event, 'reference' => $data['reference'] ?? null]);

        if ($event === 'charge.success') {
            try {
                $reference = $data['reference'];
                $transaction = Transaction::where('reference', $reference)->first();

                if ($transaction && $transaction->status === 'pending') {
                    // Process through callback logic
                    $this->processWebhookPayment($transaction, $data);
                }
            } catch (\Exception $e) {
                Log::error('Paystack webhook processing failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle Kora webhook
     */
    public function koraWebhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('x-kora-signature');
        $body = $request->getContent();
        $secretKey = config('services.kora.secret_key');

        if (!$signature || hash_hmac('sha256', $body, $secretKey) !== $signature) {
            Log::warning('Invalid Kora webhook signature', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('Kora webhook received', ['event' => $event, 'reference' => $data['reference'] ?? null]);

        if ($event === 'charge.success') {
            try {
                $reference = $data['reference'];
                $transaction = Transaction::where('reference', $reference)->first();

                if ($transaction && $transaction->status === 'pending') {
                    $this->processWebhookPayment($transaction, $data);
                }
            } catch (\Exception $e) {
                Log::error('Kora webhook processing failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle Stripe webhook
     */
    public function stripeWebhook(Request $request)
    {
        $signature = $request->header('stripe-signature');
        $body = $request->getContent();
        $webhookSecret = config('services.stripe.webhook_secret');

        // If Stripe SDK is available, use it for signature verification
        if (class_exists('\Stripe\Webhook')) {
            try {
                /** @phpstan-ignore-next-line */
                $event = \Stripe\Webhook::constructEvent($body, $signature, $webhookSecret);
            } catch (\Exception $e) {
                Log::warning('Invalid Stripe webhook signature', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            Log::info('Stripe webhook received', ['type' => $event->type]);

            if ($event->type === 'payment_intent.succeeded') {
                try {
                    $paymentIntent = $event->data->object;
                    $reference = $paymentIntent->metadata->reference ?? null;

                    if ($reference) {
                        $transaction = Transaction::where('reference', $reference)->first();

                        if ($transaction && $transaction->status === 'pending') {
                            $this->processWebhookPayment($transaction, [
                                'amount' => $paymentIntent->amount / 100,
                                'currency' => strtoupper($paymentIntent->currency),
                                'status' => 'success',
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Stripe webhook processing failed', ['error' => $e->getMessage()]);
                }
            }
        } else {
            // Fallback: basic signature verification without SDK
            Log::info('Stripe webhook received (SDK not installed)', ['has_signature' => !empty($signature)]);
            
            // Manual HMAC verification
            if ($signature && $webhookSecret) {
                $signatureParts = [];
                foreach (explode(',', $signature) as $part) {
                    list($key, $value) = explode('=', $part, 2);
                    $signatureParts[$key] = $value;
                }
                
                $timestamp = $signatureParts['t'] ?? '';
                $expectedSignature = $signatureParts['v1'] ?? '';
                $signedPayload = $timestamp . '.' . $body;
                $computedSignature = hash_hmac('sha256', $signedPayload, $webhookSecret);
                
                if (!hash_equals($computedSignature, $expectedSignature)) {
                    Log::warning('Invalid Stripe webhook signature (manual verification)');
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }

            // Parse event manually
            $event = json_decode($body, true);
            Log::info('Stripe webhook event parsed', ['type' => $event['type'] ?? 'unknown']);

            if (($event['type'] ?? '') === 'payment_intent.succeeded') {
                try {
                    $paymentIntent = $event['data']['object'] ?? [];
                    $reference = $paymentIntent['metadata']['reference'] ?? null;

                    if ($reference) {
                        $transaction = Transaction::where('reference', $reference)->first();

                        if ($transaction && $transaction->status === 'pending') {
                            $this->processWebhookPayment($transaction, [
                                'amount' => ($paymentIntent['amount'] ?? 0) / 100,
                                'currency' => strtoupper($paymentIntent['currency'] ?? 'USD'),
                                'status' => 'success',
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Stripe webhook processing failed (manual)', ['error' => $e->getMessage()]);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Process payment from webhook (idempotent)
     */
    protected function processWebhookPayment(Transaction $transaction, array $data)
    {
        // Lock transaction row for processing
        DB::transaction(function () use ($transaction, $data) {
            // Reload with lock
            $transaction = Transaction::where('id', $transaction->id)
                ->lockForUpdate()
                ->first();

            // Idempotency check
            if ($transaction->status === 'completed') {
                Log::info('Webhook payment already processed', ['transaction_id' => $transaction->id]);
                return;
            }

            $user = $transaction->user;
            $wallet = $user->wallet ?? Wallet::create([
                'user_id' => $user->id,
                'withdrawable_balance' => 0,
                'promo_credit_balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'pending_balance' => 0,
                'escrow_balance' => 0,
            ]);

            // Credit wallet
            $wallet->addWithdrawable($transaction->amount, 'Webhook payment verified');

            // Update transaction
            $transaction->update([
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => json_encode(array_merge(
                    json_decode($transaction->metadata, true) ?? [],
                    [
                        'webhook_processed_at' => now()->toIso8601String(),
                        'webhook_data' => $data,
                    ]
                )),
            ]);

            // Create ledger entry
            WalletLedger::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->id,
                'type' => 'credit',
                'category' => 'deposit',
                'amount' => $transaction->amount,
                'balance_after' => $wallet->withdrawable_balance,
                'description' => 'Webhook payment verified',
            ]);

            Log::info('Webhook payment processed', [
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
            ]);
        });
    }
}
