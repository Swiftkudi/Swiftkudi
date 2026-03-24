<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'api_key',
        'secret_key',
        'webhook_secret',
        'public_key',
        'is_sandbox',
        'is_active',
        'supported_currencies',
        'settings',
    ];

    protected $casts = [
        'is_sandbox' => 'boolean',
        'is_active' => 'boolean',
        'supported_currencies' => 'array',
        'settings' => 'array',
    ];

    // Gateway constants
    const GATEWAY_PAYSTACK = 'paystack';
    const GATEWAY_KORA = 'kora';
    const GATEWAY_STRIPE = 'stripe';

    const GATEWAYS = [
        self::GATEWAY_PAYSTACK => 'Paystack',
        self::GATEWAY_KORA => 'Kora',
        self::GATEWAY_STRIPE => 'Stripe',
    ];

    const CURRENCIES = [
        'NGN' => 'Nigerian Naira',
        'USD' => 'US Dollar',
        'USDT' => 'Tether',
    ];

    /**
     * Scope: Active gateways
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Sandbox mode
     */
    public function scopeSandbox($query)
    {
        return $query->where('is_sandbox', true);
    }

    /**
     * Scope: Live mode
     */
    public function scopeLive($query)
    {
        return $query->where('is_sandbox', false);
    }

    /**
     * Get gateway display name
     */
    public function getDisplayName(): string
    {
        return $this->display_name ?? self::GATEWAYS[$this->name] ?? $this->name;
    }

    /**
     * Check if gateway supports currency
     */
    public function supportsCurrency(string $currency): bool
    {
        return in_array($currency, $this->supported_currencies ?? []);
    }

    /**
     * Get all active gateways
     */
    public static function getActiveGateways()
    {
        return self::active()->get();
    }

    /**
     * Get gateway by name
     */
    public static function getByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }

    /**
     * Get Paystack gateway
     */
    public static function getPaystack(): ?self
    {
        return self::getByName(self::GATEWAY_PAYSTACK);
    }

    /**
     * Get Kora gateway
     */
    public static function getKora(): ?self
    {
        return self::getByName(self::GATEWAY_KORA);
    }

    /**
     * Get Stripe gateway
     */
    public static function getStripe(): ?self
    {
        return self::getByName(self::GATEWAY_STRIPE);
    }

    /**
     * Get gateway for currency
     */
    public static function getGatewayForCurrency(string $currency): ?self
    {
        return self::active()
            ->where('is_sandbox', app()->environment('production') ? false : true)
            ->get()
            ->first(function ($gateway) use ($currency) {
                return $gateway->supportsCurrency($currency);
            });
    }

    /**
     * Get sandbox URL for Paystack
     */
    public function getPaystackBaseUrl(): string
    {
        return $this->is_sandbox
            ? 'https://api.paystack.co'
            : 'https://api.paystack.co';
    }

    /**
     * Get sandbox URL for Stripe
     */
    public function getStripeBaseUrl(): string
    {
        return $this->is_sandbox
            ? 'https://api.stripe.com/v1'
            : 'https://api.stripe.com/v1';
    }

    /**
     * Get Stripe publishable key
     */
    public function getStripePublishableKey(): ?string
    {
        return $this->settings['publishable_key'] ?? $this->public_key;
    }

    /**
     * Get masked API key
     */
    public function getMaskedApiKey(): string
    {
        if (empty($this->api_key)) {
            return 'Not configured';
        }
        return substr($this->api_key, 0, 8) . '...' . substr($this->api_key, -4);
    }

    /**
     * Get transactions for this gateway
     */
    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'gateway', 'name');
    }

    /**
     * Check if gateway is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->api_key) && !empty($this->secret_key);
    }

    /**
     * Get supported currencies as array
     */
    public function getSupportedCurrenciesArray(): array
    {
        return $this->supported_currencies ?? [];
    }
}
