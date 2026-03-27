<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Gateway modes
     */
    const MODE_SANDBOX = 'sandbox';
    const MODE_LIVE = 'live';
    const MODE_MOCK = 'mock';

    /**
     * Gateways
     */
    const GATEWAY_PAYSTACK = 'paystack';
    const GATEWAY_KORA = 'kora';
    const GATEWAY_STRIPE = 'stripe';
    const GATEWAY_MOCK = 'mock';

    /**
     * Current gateway
     */
    protected $gateway;

    /**
     * Current mode
     */
    protected $mode;

    /**
     * Gateway credentials
     */
    protected $credentials = [];

    /**
     * Currency rates
     */
    protected $rates = [];

    /**
     * Constructor
     */
    public function __construct($gateway = null, $mode = null)
    {
        // Auto-detect mode based on environment if not provided
        if ($mode === null) {
            $mode = $this->detectMode();
        }

        // Auto-detect gateway from SystemSettings if not provided
        if (!$gateway) {
            $gateway = $this->detectActiveGateway();
        }

        $this->gateway = $gateway;
        $this->mode = $mode;
        $this->loadCredentials();
        $this->loadRates();
    }

    /**
     * Detect payment mode based on environment
     */
    protected function detectMode(): string
    {
        // Check SystemSetting first (admin-controlled)
        if (SystemSetting::getBool('payment_mock_enabled', false)) {
            return self::MODE_MOCK;
        }

        // Check env config for mock mode
        if (config('services.payment.mock_enabled', false)) {
            return self::MODE_MOCK;
        }

        // Check if running on local environment
        if (app()->environment('local', 'development')) {
            // Check if sandbox mode is preferred (SystemSetting or config)
            $sandboxAuto = SystemSetting::getBool('payment_sandbox_auto', 
                config('services.payment.sandbox_auto', true));
            
            if ($sandboxAuto) {
                return self::MODE_SANDBOX;
            }
        }

        // Check system setting for sandbox mode
        $gatewaySpecificSandbox = match ($this->gateway) {
            self::GATEWAY_PAYSTACK => SystemSetting::getBool('paystack_sandbox', false),
            self::GATEWAY_KORA => SystemSetting::getBool('kora_sandbox', false),
            self::GATEWAY_STRIPE => SystemSetting::getBool('stripe_sandbox', false),
            default => false,
        };

        return $gatewaySpecificSandbox ? self::MODE_SANDBOX : self::MODE_LIVE;
    }

    /**
     * Check if we're running in mock mode (for local development)
     */
    public function isMockMode(): bool
    {
        // Check SystemSetting first (admin-controlled)
        if (SystemSetting::getBool('payment_mock_enabled', false)) {
            return true;
        }

        // Fall back to env config (only if explicitly set)
        return config('services.payment.mock_enabled', false) === true;
    }

    /**
     * Get current gateway name
     */
    public function getGateway(): string
    {
        return $this->gateway ?? self::GATEWAY_PAYSTACK;
    }

    /**
     * Get current mode
     */
    public function getMode(): string
    {
        return $this->mode ?? self::MODE_LIVE;
    }

    /**
     * Get the callback URL, allowing override for local development
     */
    protected function getCallbackUrl(): string
    {
        // Check SystemSetting first (admin-controlled)
        $customCallback = SystemSetting::get('payment_callback_url');
        if ($customCallback) {
            return $customCallback;
        }

        // Check config
        $customCallback = config('services.payment.callback_url');
        if ($customCallback) {
            return $customCallback;
        }

        // Fall back to route URL
        return route('payment.callback');
    }

    /**
     * Detect which payment gateway is currently active
     */
    protected function detectActiveGateway()
    {
        // Priority: Paystack -> Kora -> Stripe
        if (SystemSetting::getBool('paystack_enabled', false)) {
            return self::GATEWAY_PAYSTACK;
        }

        if (SystemSetting::getBool('kora_enabled', false)) {
            return self::GATEWAY_KORA;
        }

        if (SystemSetting::getBool('stripe_enabled', false)) {
            return self::GATEWAY_STRIPE;
        }

        // Default to Paystack even if not enabled (for initial setup)
        return self::GATEWAY_PAYSTACK;
    }

    /**
     * Load gateway credentials from SystemSettings or config
     */
    protected function loadCredentials()
    {
        // Try SystemSetting first (admin-configured), then fall back to config (env-based)
        $publicKey = SystemSetting::get($this->gateway . '_public_key');
        $secretKey = SystemSetting::getDecrypted($this->gateway . '_secret_key');
        $merchantId = SystemSetting::get($this->gateway . '_merchant_id');

        if (!$this->hasCredentialValue($publicKey)) {
            $publicKey = config('services.' . $this->gateway . '.public_key');
        }

        if (!$this->hasCredentialValue($secretKey)) {
            $secretKey = config('services.' . $this->gateway . '.secret_key');
        }

        if (!$this->hasCredentialValue($merchantId)) {
            $merchantId = config('services.' . $this->gateway . '.merchant_id');
        }

        $secretKey = $this->normalizeSecretKey($secretKey);

        $this->credentials = [
            'public_key' => $publicKey,
            'secret_key' => $secretKey,
            'merchant_id' => $merchantId,
            'api_url' => $this->getApiUrl(),
        ];
    }

    /**
     * Check if credential has a usable value
     */
    protected function hasCredentialValue($value): bool
    {
        return is_string($value) ? trim($value) !== '' : !empty($value);
    }

    /**
     * Normalize secret key by removing accidental Bearer prefix
     */
    protected function normalizeSecretKey($secretKey)
    {
        if (!is_string($secretKey)) {
            return $secretKey;
        }

        $normalized = trim($secretKey);
        if (stripos($normalized, 'Bearer ') === 0) {
            $normalized = trim(substr($normalized, 7));
        }

        return $normalized;
    }

    /**
     * Get API URL based on mode
     */
    protected function getApiUrl()
    {
        // Mock mode doesn't need an API URL
        if ($this->mode === self::MODE_MOCK || $this->gateway === self::GATEWAY_MOCK) {
            return '';
        }

        $urls = [
            self::GATEWAY_PAYSTACK => [
                self::MODE_SANDBOX => 'https://api.paystack.co',
                self::MODE_LIVE => 'https://api.paystack.co',
                self::MODE_MOCK => 'https://api.paystack.co', // Mock uses same URL
            ],
            self::GATEWAY_KORA => [
                self::MODE_SANDBOX => 'https://api.korapay.com',
                self::MODE_LIVE => 'https://api.korapay.com',
                self::MODE_MOCK => 'https://api.korapay.com', // Mock uses same URL
            ],
            self::GATEWAY_STRIPE => [
                self::MODE_SANDBOX => 'https://api.stripe.com/v1',
                self::MODE_LIVE => 'https://api.stripe.com/v1',
                self::MODE_MOCK => 'https://api.stripe.com/v1', // Mock uses same URL
            ],
        ];

        // Fallback to live mode if the specific mode isn't defined
        if (!isset($urls[$this->gateway][$this->mode])) {
            return $urls[$this->gateway][self::MODE_LIVE] ?? '';
        }

        return $urls[$this->gateway][$this->mode];
    }

    /**
     * Load currency rates
     */
    protected function loadRates()
    {
        // Default rates (would fetch from API in production)
        $this->rates = [
            'NGN' => 1,
            'USD' => 1500,
            'USDT' => 1520,
        ];

        // Try to fetch from API
        try {
            $response = Http::get('https://api.exchangerate-api.com/v4/latest/NGN');
            if ($response->successful()) {
                $data = $response->json();
                $this->rates['USD'] = $data['rates']['USD'] ?? 1500;
                $this->rates['USDT'] = $data['rates']['USDT'] ?? 1520;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch currency rates', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Convert amount to NGN
     */
    public function convertToNgn($amount, $currency)
    {
        if ($currency === 'NGN') {
            return $amount;
        }

        $rate = $this->rates[$currency] ?? 1500;
        return $amount * $rate;
    }

    /**
     * Convert amount from NGN
     */
    public function convertFromNgn($amount, $currency)
    {
        if ($currency === 'NGN') {
            return $amount;
        }

        $rate = $this->rates[$currency] ?? 1500;
        return $amount / $rate;
    }

    /**
     * Get current exchange rates
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * Set gateway mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        $this->loadCredentials();
        return $this;
    }

    /**
     * Set gateway
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
        $this->loadCredentials();
        return $this;
    }

    /**
     * Initialize payment
     */
    public function initializePayment(User $user, $amount, $currency, $description)
    {
        // Handle mock mode for local development
        if ($this->isMockMode()) {
            return $this->processMockPayment($user, $amount, $currency, $description);
        }

        $amountInNgn = $this->convertToNgn($amount, $currency);

        $data = [
            'amount' => $amountInNgn * 100, // Convert to kobo
            'currency' => 'NGN',
            'email' => $user->email,
            'reference' => $this->generateReference(),
            'callback_url' => $this->getCallbackUrl(),
            'metadata' => [
                'user_id' => $user->id,
                'currency' => $currency,
                'original_amount' => $amount,
                'description' => $description,
            ],
        ];

        return $this->processPayment($data);
    }

    /**
     * Process mock payment for local development
     */
    protected function processMockPayment(User $user, $amount, $currency, $description): array
    {
        $reference = $this->generateReference();
        $amountInNgn = $this->convertToNgn($amount, $currency);

        Log::info('Mock payment initialized', [
            'user_id' => $user->id,
            'amount' => $amount,
            'currency' => $currency,
            'amount_in_ngn' => $amountInNgn,
            'reference' => $reference,
        ]);

        // Return a mock authorization URL that redirects back to our callback
        $mockCallbackUrl = $this->getCallbackUrl() . '?reference=' . $reference . '&status=success';

        return [
            'success' => true,
            'authorization_url' => $mockCallbackUrl,
            'reference' => $reference,
            'mock' => true,
            'message' => 'Mock payment mode - click to simulate successful payment',
        ];
    }

    /**
     * Process payment based on gateway
     */
    protected function processPayment($data)
    {
        switch ($this->gateway) {
            case self::GATEWAY_PAYSTACK:
                return $this->processPaystack($data);
            case self::GATEWAY_KORA:
                return $this->processKora($data);
            case self::GATEWAY_STRIPE:
                return $this->processStripe($data);
            case self::GATEWAY_MOCK:
                return $this->processMockPaymentData($data);
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid payment gateway',
                ];
        }
    }

    /**
     * Process mock payment data
     */
    protected function processMockPaymentData($data): array
    {
        $reference = $data['reference'] ?? $this->generateReference();
        
        return [
            'success' => true,
            'authorization_url' => $this->getCallbackUrl() . '?reference=' . $reference . '&status=success&mock=true',
            'reference' => $reference,
            'mock' => true,
        ];
    }

    /**
     * Process Paystack payment
     */
    protected function processPaystack($data)
    {
        try {
            if (!$this->hasCredentialValue($this->credentials['secret_key'] ?? null)) {
                return [
                    'success' => false,
                    'message' => 'Paystack secret key is missing. Set it in admin settings or PAYSTACK_SECRET_KEY in .env.',
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                'Content-Type' => 'application/json',
            ])->post($this->credentials['api_url'] . '/transaction/initialize', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'authorization_url' => $response->json()['data']['authorization_url'],
                    'reference' => $data['reference'],
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack payment error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Payment processing error',
            ];
        }
    }

    /**
     * Process Kora payment
     */
    protected function processKora($data)
    {
        try {
            if (!$this->hasCredentialValue($this->credentials['secret_key'] ?? null)) {
                return [
                    'success' => false,
                    'message' => 'Kora secret key is missing. Set it in admin settings or KORA_SECRET_KEY in .env.',
                ];
            }

            $koraPayload = [
                'amount' => ((float) ($data['amount'] ?? 0)) / 100,
                'currency' => $data['currency'] ?? 'NGN',
                'reference' => $data['reference'] ?? null,
                'tx_ref' => $data['reference'] ?? null,
                'redirect_url' => $data['callback_url'] ?? route('payment.callback'),
                'callback_url' => $data['callback_url'] ?? route('payment.callback'),
                'customer' => [
                    'email' => $data['email'] ?? null,
                ],
                'metadata' => $data['metadata'] ?? [],
            ];

            $response = Http::timeout(25)->withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                'Content-Type' => 'application/json',
            ])->post($this->credentials['api_url'] . '/charges/initialize', $koraPayload);

            if (!$response->successful()) {
                $response = Http::timeout(25)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                    'Content-Type' => 'application/json',
                ])->post($this->credentials['api_url'] . '/charges', $koraPayload);
            }

            $json = $response->json() ?? [];
            $payload = $json['data'] ?? [];
            $authorizationUrl = $payload['checkout_url'] ?? $payload['redirect_url'] ?? $payload['payment_link'] ?? null;

            if ($response->successful() && $authorizationUrl) {
                return [
                    'success' => true,
                    'authorization_url' => $authorizationUrl,
                    'reference' => $data['reference'],
                ];
            }

            return [
                'success' => false,
                'message' => $json['message'] ?? $json['error'] ?? 'Kora payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Kora payment error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Kora payment processing error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process Stripe payment
     */
    protected function processStripe($data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->post($this->credentials['api_url'] . '/payment_intents', [
                'amount' => $data['amount'],
                'currency' => strtolower($data['currency']),
                'receipt_email' => $data['email'],
                'metadata' => $data['metadata'],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'client_secret' => $response->json()['client_secret'],
                    'reference' => $data['reference'],
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['error']['message'] ?? 'Payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Payment processing error',
            ];
        }
    }

    /**
     * Verify payment
     */
    public function verifyPayment($reference)
    {
        // Handle mock mode verification
        if ($this->isMockMode()) {
            return $this->verifyMockPayment($reference);
        }

        switch ($this->gateway) {
            case self::GATEWAY_PAYSTACK:
                return $this->verifyPaystack($reference);
            case self::GATEWAY_KORA:
                return $this->verifyKora($reference);
            case self::GATEWAY_STRIPE:
                return $this->verifyStripe($reference);
            case self::GATEWAY_MOCK:
                return $this->verifyMockPayment($reference);
            default:
                return ['success' => false, 'message' => 'Invalid gateway'];
        }
    }

    /**
     * Verify mock payment (always succeeds in mock mode)
     */
    protected function verifyMockPayment($reference): array
    {
        // Find the transaction to get the amount
        $transaction = Transaction::where('reference', $reference)->first();
        
        $amount = 0;
        $currency = 'NGN';
        
        if ($transaction) {
            // Get the original amount from the transaction
            $amount = (float) $transaction->amount;
            $currency = $transaction->currency ?? 'NGN';
            
            // Convert to NGN if needed
            if ($currency !== 'NGN') {
                $amount = $this->convertToNgn($amount, $currency);
                $currency = 'NGN';
            }
        }
        
        Log::info('Mock payment verified', [
            'reference' => $reference,
            'amount' => $amount,
            'currency' => $currency,
        ]);

        return [
            'success' => true,
            'amount' => $amount,
            'currency' => $currency,
            'metadata' => ['mock' => true],
            'status' => 'success',
        ];
    }

    /**
     * Verify Paystack payment
     */
    protected function verifyPaystack($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
            ])->get($this->credentials['api_url'] . '/transaction/verify/' . $reference);

            if ($response->successful()) {
                $data = $response->json()['data'];
                return [
                    'success' => $data['status'] === 'success',
                    'amount' => $data['amount'] / 100,
                    'currency' => $data['currency'],
                    'metadata' => $data['metadata'] ?? [],
                ];
            }

            return ['success' => false, 'message' => 'Verification failed'];
        } catch (\Exception $e) {
            Log::error('Paystack verification error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Verification error'];
        }
    }

    /**
     * Verify Kora payment
     */
    protected function verifyKora($reference)
    {
        try {
            $response = Http::timeout(25)->withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
            ])->get($this->credentials['api_url'] . '/transactions/' . $reference);

            if (!$response->successful()) {
                $response = Http::timeout(25)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                ])->get($this->credentials['api_url'] . '/transactions/verify/' . $reference);
            }

            if ($response->successful()) {
                $json = $response->json() ?? [];
                $data = $json['data'] ?? [];
                $status = strtolower((string) ($data['status'] ?? ''));
                return [
                    'success' => in_array($status, ['successful', 'success', 'succeeded']),
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'metadata' => $data['metadata'] ?? [],
                ];
            }

            return ['success' => false, 'message' => 'Verification failed'];
        } catch (\Exception $e) {
            Log::error('Kora verification error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Verification error'];
        }
    }

    /**
     * Verify Stripe payment
     */
    protected function verifyStripe($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
            ])->get($this->credentials['api_url'] . '/payment_intents/' . $reference);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => $data['status'] === 'succeeded',
                    'amount' => $data['amount'] / 100,
                    'currency' => strtoupper($data['currency']),
                    'metadata' => $data['metadata'] ?? [],
                ];
            }

            return ['success' => false, 'message' => 'Verification failed'];
        } catch (\Exception $e) {
            Log::error('Stripe verification error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Verification error'];
        }
    }

    /**
     * Handle payment callback
     */
    public function handleCallback($data)
    {
        $reference = $data['reference'] ?? $data['tx_ref'] ?? null;

        if (!$reference) {
            return ['success' => false, 'message' => 'No reference provided'];
        }

        return $this->verifyPayment($reference);
    }

    /**
     * Generate payment reference
     */
    protected function generateReference()
    {
        return 'ED_' . strtoupper(bin2hex(random_bytes(8)));
    }

    /**
     * Detect user currency based on IP
     */
    public static function detectCurrencyFromIp()
    {
        $ngnCountries = ['NG']; // Nigeria

        try {
            $response = Http::get('http://ip-api.com/json/' . request()->ip());
            if ($response->successful()) {
                $data = $response->json();
                $country = $data['countryCode'] ?? '';

                if (in_array($country, $ngnCountries)) {
                    return Currency::CURRENCY_NGN;
                }

                return Currency::CURRENCY_USD;
            }
        } catch (\Exception $e) {
            Log::warning('IP geolocation failed', ['error' => $e->getMessage()]);
        }

        // Default to NGN
        return Currency::CURRENCY_NGN;
    }

    /**
     * Get supported currencies for gateway
     */
    public function getSupportedCurrencies()
    {
        $gateways = [
            self::GATEWAY_PAYSTACK => ['NGN', 'USD'],
            self::GATEWAY_KORA => ['NGN', 'USD'],
            self::GATEWAY_STRIPE => ['USD', 'USDT'],
        ];

        return $gateways[$this->gateway] ?? ['NGN'];
    }

    /**
     * Credit user wallet
     */
    public function creditUserWallet(User $user, $amount, $currency, $description)
    {
        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'withdrawable_balance' => 0,
            'promo_credit_balance' => 0,
            'currency' => $currency,
        ]);

        // Update currency if different
        if ($wallet->currency !== $currency) {
            $wallet->currency = $currency;
            $wallet->save();
        }

        $wallet->addWithdrawable($amount, 'deposit');

        // Create transaction record
        Transaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'type' => Transaction::TYPE_DEPOSIT,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'completed',
            'description' => $description,
            'reference' => $this->generateReference(),
        ]);

        return true;
    }
}
