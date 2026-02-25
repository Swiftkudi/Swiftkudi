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

    /**
     * Gateways
     */
    const GATEWAY_PAYSTACK = 'paystack';
    const GATEWAY_KORA = 'kora';
    const GATEWAY_STRIPE = 'stripe';

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
    public function __construct($gateway = null, $mode = self::MODE_LIVE)
    {
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
        $this->credentials = [
            'public_key' => SystemSetting::get($this->gateway . '_public_key') 
                ?? config('services.' . $this->gateway . '.public_key'),
            'secret_key' => SystemSetting::getDecrypted($this->gateway . '_secret_key') 
                ?? config('services.' . $this->gateway . '.secret_key'),
            'merchant_id' => SystemSetting::get($this->gateway . '_merchant_id') 
                ?? config('services.' . $this->gateway . '.merchant_id'),
            'api_url' => $this->getApiUrl(),
        ];
    }

    /**
     * Get API URL based on mode
     */
    protected function getApiUrl()
    {
        $urls = [
            self::GATEWAY_PAYSTACK => [
                self::MODE_SANDBOX => 'https://api.paystack.co',
                self::MODE_LIVE => 'https://api.paystack.co',
            ],
            self::GATEWAY_KORA => [
                self::MODE_SANDBOX => 'https://api.korapay.com',
                self::MODE_LIVE => 'https://api.korapay.com',
            ],
            self::GATEWAY_STRIPE => [
                self::MODE_SANDBOX => 'https://api.stripe.com/v1',
                self::MODE_LIVE => 'https://api.stripe.com/v1',
            ],
        ];

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
        $amountInNgn = $this->convertToNgn($amount, $currency);

        $data = [
            'amount' => $amountInNgn * 100, // Convert to kobo
            'currency' => 'NGN',
            'email' => $user->email,
            'reference' => $this->generateReference(),
            'callback_url' => route('payment.callback'),
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
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid payment gateway',
                ];
        }
    }

    /**
     * Process Paystack payment
     */
    protected function processPaystack($data)
    {
        try {
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                'Content-Type' => 'application/json',
            ])->post($this->credentials['api_url'] . '/charges', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'authorization_url' => $response->json()['data']['checkout_url'],
                    'reference' => $data['reference'],
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Payment initialization failed',
            ];
        } catch (\Exception $e) {
            Log::error('Kora payment error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Payment processing error',
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
        switch ($this->gateway) {
            case self::GATEWAY_PAYSTACK:
                return $this->verifyPaystack($reference);
            case self::GATEWAY_KORA:
                return $this->verifyKora($reference);
            case self::GATEWAY_STRIPE:
                return $this->verifyStripe($reference);
            default:
                return ['success' => false, 'message' => 'Invalid gateway'];
        }
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
            ])->get($this->credentials['api_url'] . '/transactions/' . $reference);

            if ($response->successful()) {
                $data = $response->json()['data'];
                return [
                    'success' => $data['status'] === 'successful',
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
