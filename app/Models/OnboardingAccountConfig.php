<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OnboardingAccountConfig extends Model
{
    protected $table = 'onboarding_account_configs';

    protected $fillable = [
        'account_type',
        'activation_required',
        'activation_fee',
        'enabled',
        'description',
    ];

    protected $casts = [
        'activation_required' => 'boolean',
        'activation_fee' => 'decimal:2',
        'enabled' => 'boolean',
    ];

    const ACCOUNT_TYPES = [
        'buyer' => 'Buyer',
        'earner' => 'Earner',
        'task_creator' => 'Task Creator',
        'freelancer' => 'Freelancer',
        'digital_seller' => 'Digital Seller',
        'growth_seller' => 'Growth Seller',
    ];

    const DEFAULT_CONFIGS = [
        ['account_type' => 'buyer', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Buyer account - no activation fee'],
        ['account_type' => 'earner', 'activation_required' => true, 'activation_fee' => 1500, 'enabled' => true, 'description' => 'Earner account - requires activation fee'],
        ['account_type' => 'task_creator', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Task Creator account - free activation'],
        ['account_type' => 'freelancer', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Freelancer account - free activation'],
        ['account_type' => 'digital_seller', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Digital Seller account - free activation'],
        ['account_type' => 'growth_seller', 'activation_required' => false, 'activation_fee' => 0, 'enabled' => true, 'description' => 'Growth Seller account - free activation'],
    ];

    public static function getConfig(string $accountType): ?self
    {
        return self::where('account_type', $accountType)
            ->where('enabled', true)
            ->first();
    }

    public static function isActivationRequired(string $accountType): bool
    {
        $config = self::getConfig($accountType);
        return $config ? $config->activation_required : false;
    }

    public static function getActivationFee(string $accountType): float
    {
        $config = self::getConfig($accountType);
        return $config ? (float) $config->activation_fee : 0;
    }

    public static function getAllConfigs(): array
    {
        $configs = self::all()->keyBy('account_type');
        $result = [];
        
        foreach (self::ACCOUNT_TYPES as $type => $label) {
            $result[$type] = [
                'label' => $label,
                'enabled' => $configs->has($type) ? $configs[$type]->enabled : false,
                'activation_required' => $configs->has($type) ? $configs[$type]->activation_required : false,
                'activation_fee' => $configs->has($type) ? (float) $configs[$type]->activation_fee : 0,
            ];
        }
        
        return $result;
    }

    public static function resetToDefaults(): void
    {
        foreach (self::DEFAULT_CONFIGS as $default) {
            $config = self::where('account_type', $default['account_type'])->first();
            
            if ($config) {
                $config->update($default);
            } else {
                self::create($default);
            }
        }
    }
}