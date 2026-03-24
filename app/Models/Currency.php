<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SystemSetting;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate_to_ngn',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'rate_to_ngn' => 'decimal:4',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Currencies
     */
    public const CURRENCY_NGN = 'NGN';
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_USDT = 'USDT';

    /**
     * Relationship: Wallets
     */
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Relationship: Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get default currency - uses SystemSetting for default currency
     * This ensures admin panel settings are respected
     */
    public static function getDefault()
    {
        // First check SystemSetting for default currency
        $defaultCurrency = SystemSetting::get('default_currency', 'NGN');
        
        $currency = self::where('code', $defaultCurrency)->first();
        
        if ($currency && $currency->is_active) {
            return $currency;
        }
        
        // Fallback: check is_default flag in currencies table
        $defaultFromDb = self::where('is_default', true)->first();
        
        if ($defaultFromDb && $defaultFromDb->is_active) {
            return $defaultFromDb;
        }
        
        // Final fallback: return NGN
        return self::ngn() ?? self::first();
    }

    /**
     * Get default currency code from SystemSetting
     */
    public static function getDefaultCode(): string
    {
        return SystemSetting::get('default_currency', 'NGN');
    }

    /**
     * Check if a specific currency is enabled in settings
     */
    public static function isEnabled(string $code): bool
    {
        $enabledKey = 'currency_' . strtolower($code) . '_enabled';
        return SystemSetting::getBool($enabledKey, $code === 'NGN'); // NGN is enabled by default
    }

    /**
     * Get NGN currency
     */
    public static function ngn()
    {
        return self::where('code', self::CURRENCY_NGN)->first();
    }

    /**
     * Get USD currency
     */
    public static function usd()
    {
        return self::where('code', self::CURRENCY_USD)->first();
    }

    /**
     * Get USDT currency
     */
    public static function usdt()
    {
        return self::where('code', self::CURRENCY_USDT)->first();
    }

    /**
     * Convert amount to NGN
     */
    public function toNgn(float $amount): float
    {
        return $amount * $this->rate_to_ngn;
    }

    /**
     * Convert amount from NGN
     */
    public function fromNgn(float $amount): float
    {
        return $amount / $this->rate_to_ngn;
    }

    /**
     * Convert amount to another currency
     */
    public function convert(float $amount, Currency $toCurrency): float
    {
        $amountInNgn = $this->toNgn($amount);
        return $toCurrency->fromNgn($amountInNgn);
    }

    /**
     * Get active currencies
     */
    public static function getActive()
    {
        return self::where('is_active', true)->get();
    }

    /**
     * Get currency symbol
     */
    public function getSymbolAttribute(): string
    {
        return $this->symbol ?? '$';
    }

    /**
     * Format amount with currency
     */
    public function format(float $amount): string
    {
        return $this->symbol . number_format($amount, 2);
    }

    /**
     * Seed default currencies
     */
    public static function seedDefaults()
    {
        // NGN (base currency)
        self::updateOrCreate(
            ['code' => self::CURRENCY_NGN],
            [
                'name' => 'Nigerian Naira',
                'symbol' => '₦',
                'rate_to_ngn' => 1,
                'is_active' => true,
                'is_default' => true,
            ]
        );

        // USD
        self::updateOrCreate(
            ['code' => self::CURRENCY_USD],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'rate_to_ngn' => 1500, // 1 USD = 1500 NGN (example rate)
                'is_active' => true,
                'is_default' => false,
            ]
        );

        // USDT
        self::updateOrCreate(
            ['code' => self::CURRENCY_USDT],
            [
                'name' => 'Tether',
                'symbol' => '₮',
                'rate_to_ngn' => 1520, // 1 USDT = 1520 NGN (example rate)
                'is_active' => true,
                'is_default' => false,
            ]
        );
    }
}
