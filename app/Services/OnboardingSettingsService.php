<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\OnboardingAccountConfig;
use Illuminate\Support\Facades\Cache;

class OnboardingSettingsService
{
    // Setting keys
    const ONBOARDING_ENABLED = 'onboarding_enabled';
    const BUYER_ONBOARDING_ENABLED = 'buyer_onboarding_enabled';
    const BUYER_CATEGORY_SELECTION_REQUIRED = 'buyer_category_selection_required';
    const BUYER_MIN_CATEGORIES = 'buyer_min_categories';
    const BUYER_MAX_CATEGORIES = 'buyer_max_categories';
    const BUYER_DEFAULT_TO_ALL_CATEGORIES = 'buyer_default_to_all_categories';
    const BUYER_ALLOW_CATEGORY_UPDATES = 'buyer_allow_category_updates';

    const EARNER_ONBOARDING_ENABLED = 'earner_onboarding_enabled';
    // DEPRECATED: Use OnboardingAccountConfig instead
    const EARNER_ACTIVATION_REQUIRED = 'earner_activation_required';
    const EARNER_ACTIVATION_FEE = 'earner_activation_fee';

    const TASK_CREATOR_ONBOARDING_ENABLED = 'task_creator_onboarding_enabled';
    const TASK_CREATOR_FIRST_TASK_REQUIRED = 'task_creator_first_task_required';

    const AUTO_COMPLETE_ONBOARDING = 'auto_complete_onboarding';

    const FREELANCER_ONBOARDING_ENABLED = 'freelancer_onboarding_enabled';
    // DEPRECATED: Use OnboardingAccountConfig instead
    const FREELANCER_ACTIVATION_REQUIRED = 'freelancer_activation_required';
    const FREELANCER_PROFILE_REQUIRED = 'freelancer_profile_required';
    const FREELANCER_SERVICE_REQUIRED = 'freelancer_service_required';

    const DIGITAL_SELLER_ONBOARDING_ENABLED = 'digital_seller_onboarding_enabled';
    // DEPRECATED: Use OnboardingAccountConfig instead
    const DIGITAL_ACTIVATION_REQUIRED = 'digital_activation_required';
    const DIGITAL_PRODUCT_REQUIRED = 'digital_product_required';

    const GROWTH_SELLER_ONBOARDING_ENABLED = 'growth_seller_onboarding_enabled';
    // DEPRECATED: Use OnboardingAccountConfig instead
    const GROWTH_ACTIVATION_REQUIRED = 'growth_activation_required';
    const GROWTH_LISTING_REQUIRED = 'growth_listing_required';

    // Cache key
    const CACHE_KEY = 'onboarding_settings';

    /**
     * Get activation config for a specific account type (CENTRALIZED SOURCE)
     */
    public static function getActivationConfig(string $accountType): ?OnboardingAccountConfig
    {
        return OnboardingAccountConfig::getConfig($accountType);
    }

    /**
     * Check if activation is required for account type (CENTRALIZED)
     */
    public static function isActivationRequired(string $accountType): bool
    {
        $config = self::getActivationConfig($accountType);
        return $config ? $config->activation_required : false;
    }

    /**
     * Get activation fee for account type (CENTRALIZED)
     */
    public static function getActivationFee(string $accountType): float
    {
        $config = self::getActivationConfig($accountType);
        return $config ? (float) $config->activation_fee : 0;
    }

    /**
     * Get all account type configurations (CENTRALIZED)
     */
    public static function getAllAccountConfigs(): array
    {
        return OnboardingAccountConfig::getAllConfigs();
    }

    /**
     * Update account type configuration
     */
    public static function updateAccountConfig(string $accountType, array $data): bool
    {
        $config = OnboardingAccountConfig::where('account_type', $accountType)->first();
        
        if (!$config) {
            $config = new OnboardingAccountConfig();
            $config->account_type = $accountType;
        }

        if (isset($data['activation_required'])) {
            $config->activation_required = $data['activation_required'];
        }
        if (isset($data['activation_fee'])) {
            $config->activation_fee = $data['activation_fee'];
        }
        if (isset($data['enabled'])) {
            $config->enabled = $data['enabled'];
        }
        if (isset($data['description'])) {
            $config->description = $data['description'];
        }

        return $config->save();
    }

    /**
     * Legacy support: get activation required for earner (falls back to config)
     */
    public static function isEarnerActivationRequired(): bool
    {
        return self::isActivationRequired('earner');
    }

    /**
     * Legacy support: get activation fee for earner (falls back to config)
     */
    public static function getEarnerActivationFee(): float
    {
        return self::getActivationFee('earner');
    }

    /**
     * Get all onboarding settings
     */
    public static function getAllSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return [
                // General
                'onboarding_enabled' => SystemSetting::get(self::ONBOARDING_ENABLED, true),
                'auto_complete_onboarding' => SystemSetting::get(self::AUTO_COMPLETE_ONBOARDING, true),
                'buyer_onboarding_enabled' => SystemSetting::get(self::BUYER_ONBOARDING_ENABLED, true),

                // Buyer settings
                'buyer_category_selection_required' => SystemSetting::get(self::BUYER_CATEGORY_SELECTION_REQUIRED, true),
                'buyer_min_categories' => SystemSetting::get(self::BUYER_MIN_CATEGORIES, 1),
                'buyer_max_categories' => SystemSetting::get(self::BUYER_MAX_CATEGORIES, 10),
                'buyer_default_to_all_categories' => SystemSetting::get(self::BUYER_DEFAULT_TO_ALL_CATEGORIES, false),
                'buyer_allow_category_updates' => SystemSetting::get(self::BUYER_ALLOW_CATEGORY_UPDATES, true),

                // Earner settings (activation now in OnboardingAccountConfig)
                'earner_onboarding_enabled' => SystemSetting::get(self::EARNER_ONBOARDING_ENABLED, true),

                // Task creator settings
                'task_creator_onboarding_enabled' => SystemSetting::get(self::TASK_CREATOR_ONBOARDING_ENABLED, true),
                'task_creator_first_task_required' => SystemSetting::get(self::TASK_CREATOR_FIRST_TASK_REQUIRED, true),

                // Freelancer settings (activation now in OnboardingAccountConfig)
                'freelancer_onboarding_enabled' => SystemSetting::get(self::FREELANCER_ONBOARDING_ENABLED, true),
                'freelancer_profile_required' => SystemSetting::get(self::FREELANCER_PROFILE_REQUIRED, true),
                'freelancer_service_required' => SystemSetting::get(self::FREELANCER_SERVICE_REQUIRED, true),

                // Digital seller settings (activation now in OnboardingAccountConfig)
                'digital_seller_onboarding_enabled' => SystemSetting::get(self::DIGITAL_SELLER_ONBOARDING_ENABLED, true),
                'digital_product_required' => SystemSetting::get(self::DIGITAL_PRODUCT_REQUIRED, true),

                // Growth seller settings (activation now in OnboardingAccountConfig)
                'growth_seller_onboarding_enabled' => SystemSetting::get(self::GROWTH_SELLER_ONBOARDING_ENABLED, true),
                'growth_listing_required' => SystemSetting::get(self::GROWTH_LISTING_REQUIRED, true),
            ];
        });
    }

    /**
     * Get a specific setting
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::getAllSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        // Map setting keys to SystemSetting groups
        $groupMapping = [
            self::ONBOARDING_ENABLED => SystemSetting::GROUP_GENERAL,
            self::AUTO_COMPLETE_ONBOARDING => SystemSetting::GROUP_GENERAL,
            self::BUYER_ONBOARDING_ENABLED => SystemSetting::GROUP_GENERAL,
            self::BUYER_CATEGORY_SELECTION_REQUIRED => SystemSetting::GROUP_GENERAL,
            self::BUYER_MIN_CATEGORIES => SystemSetting::GROUP_GENERAL,
            self::BUYER_MAX_CATEGORIES => SystemSetting::GROUP_GENERAL,
            self::BUYER_DEFAULT_TO_ALL_CATEGORIES => SystemSetting::GROUP_GENERAL,
            self::BUYER_ALLOW_CATEGORY_UPDATES => SystemSetting::GROUP_GENERAL,

            self::EARNER_ONBOARDING_ENABLED => SystemSetting::GROUP_GENERAL,
            self::EARNER_ACTIVATION_REQUIRED => SystemSetting::GROUP_GENERAL,
            self::EARNER_ACTIVATION_FEE => SystemSetting::GROUP_GENERAL,

            self::TASK_CREATOR_ONBOARDING_ENABLED => SystemSetting::GROUP_GENERAL,
            self::TASK_CREATOR_FIRST_TASK_REQUIRED => SystemSetting::GROUP_GENERAL,

            self::FREELANCER_ONBOARDING_ENABLED => SystemSetting::GROUP_GENERAL,
            self::FREELANCER_ACTIVATION_REQUIRED => SystemSetting::GROUP_GENERAL,
            self::FREELANCER_PROFILE_REQUIRED => SystemSetting::GROUP_GENERAL,
            self::FREELANCER_SERVICE_REQUIRED => SystemSetting::GROUP_GENERAL,

            self::DIGITAL_SELLER_ONBOARDING_ENABLED => SystemSetting::GROUP_GENERAL,
            self::DIGITAL_ACTIVATION_REQUIRED => SystemSetting::GROUP_GENERAL,
            self::DIGITAL_PRODUCT_REQUIRED => SystemSetting::GROUP_GENERAL,

            self::GROWTH_SELLER_ONBOARDING_ENABLED => SystemSetting::GROUP_GENERAL,
            self::GROWTH_ACTIVATION_REQUIRED => SystemSetting::GROUP_GENERAL,
            self::GROWTH_LISTING_REQUIRED => SystemSetting::GROUP_GENERAL,
        ];

        $group = $groupMapping[$key] ?? SystemSetting::GROUP_GENERAL;
        $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'text');

        SystemSetting::set($key, $value, $group, $type);

        // Clear cache
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Alias for getAllSettings for compatibility with existing callers.
     */
    public function getSettings(): array
    {
        return self::getAllSettings();
    }

    /**
     * Check if onboarding is enabled globally
     */
    public static function isOnboardingEnabled(): bool
    {
        return self::get(self::ONBOARDING_ENABLED, true);
    }

    /**
     * Check if buyer onboarding is enabled
     */
    public static function isBuyerOnboardingEnabled(): bool
    {
        return self::get(self::BUYER_ONBOARDING_ENABLED, true);
    }

    /**
     * Check if buyer category selection is required
     */
    public static function isBuyerCategorySelectionRequired(): bool
    {
        return self::get(self::BUYER_CATEGORY_SELECTION_REQUIRED, true);
    }

    /**
     * Get buyer category limits
     */
    public static function getBuyerCategoryLimits(): array
    {
        return [
            'min' => self::get(self::BUYER_MIN_CATEGORIES, 1),
            'max' => self::get(self::BUYER_MAX_CATEGORIES, 10),
        ];
    }

    /**
     * Check if buyers should default to all categories
     */
    public static function shouldBuyerDefaultToAllCategories(): bool
    {
        return self::get(self::BUYER_DEFAULT_TO_ALL_CATEGORIES, false);
    }

    /**
     * Check if buyers can update their categories
     */
    public static function canBuyerUpdateCategories(): bool
    {
        return self::get(self::BUYER_ALLOW_CATEGORY_UPDATES, true);
    }

    /**
     * Get onboarding requirements for an account type
     */
    public static function getOnboardingRequirements(string $accountType): array
    {
        $settings = self::getAllSettings();

        switch ($accountType) {
            case 'buyer':
                return [
                    'enabled' => $settings['buyer_onboarding_enabled'],
                    'category_selection_required' => $settings['buyer_category_selection_required'],
                    'min_categories' => $settings['buyer_min_categories'],
                    'max_categories' => $settings['buyer_max_categories'],
                    'default_to_all' => $settings['buyer_default_to_all_categories'],
                    'allow_updates' => $settings['buyer_allow_category_updates'],
                ];

            case 'earner':
                return [
                    'enabled' => $settings['earner_onboarding_enabled'],
                ];

            case 'task_creator':
                return [
                    'enabled' => $settings['task_creator_onboarding_enabled'],
                    'first_task_required' => $settings['task_creator_first_task_required'],
                ];

            case 'freelancer':
                return [
                    'enabled' => $settings['freelancer_onboarding_enabled'],
                    'profile_required' => $settings['freelancer_profile_required'],
                    'service_required' => $settings['freelancer_service_required'],
                ];

            case 'digital_seller':
                return [
                    'enabled' => $settings['digital_seller_onboarding_enabled'],
                    'product_required' => $settings['digital_product_required'],
                ];

            case 'growth_seller':
                return [
                    'enabled' => $settings['growth_seller_onboarding_enabled'],
                    'listing_required' => $settings['growth_listing_required'],
                ];

            default:
                return ['enabled' => false];
        }
    }

    /**
     * Check if a user has completed onboarding based on their account type
     * Automatically marks onboarding as completed if all requirements are met
     */
    public static function hasUserCompletedOnboarding(User $user): bool
    {
        if (!$user->account_type) {
            return false;
        }

        $requirements = self::getOnboardingRequirements($user->account_type);

        if (!$requirements['enabled']) {
            return true; // If onboarding is disabled for this type, consider it complete
        }

        $isComplete = false;

        switch ($user->account_type) {
            case 'buyer':
                if ($requirements['category_selection_required']) {
                    $isComplete = $user->buyer_onboarding_completed;
                } else {
                    $isComplete = true;
                }
                break;

            case 'earner':
                $activationRequired = self::isActivationRequired('earner');
                if ($activationRequired) {
                    $isComplete = $user->wallet && $user->wallet->is_activated;
                } else {
                    $isComplete = $user->onboarding_completed;
                }
                break;

            case 'task_creator':
                $activationRequired = self::isActivationRequired('task_creator');
                if ($activationRequired) {
                    $isComplete = $user->wallet && $user->wallet->is_activated;
                }
                if ($requirements['first_task_required']) {
                    $isComplete = $user->first_task_created;
                } else {
                    $isComplete = $user->onboarding_completed;
                }
                break;

            case 'freelancer':
                $checks = [];
                $activationRequired = self::isActivationRequired('freelancer');
                if ($activationRequired) {
                    $isComplete = $user->wallet && $user->wallet->is_activated;
                }
                if ($requirements['profile_required']) {
                    $checks[] = $user->freelancer_profile_completed;
                }
                if ($requirements['service_required']) {
                    $checks[] = $user->freelancer_service_created;
                }
                $isComplete = !in_array(false, $checks, true);
                break;

            case 'digital_seller':
                $checks = [];
                $activationRequired = self::isActivationRequired('digital_seller');
                if ($activationRequired) {
                    $isComplete = $user->wallet && $user->wallet->is_activated;
                }
                if ($requirements['product_required']) {
                    $checks[] = $user->digital_product_uploaded;
                }
                $isComplete = !in_array(false, $checks, true);
                break;

            case 'growth_seller':
                $checks = [];
                $activationRequired = self::isActivationRequired('growth_seller');
                if ($activationRequired) {
                    $isComplete = $user->wallet && $user->wallet->is_activated;
                }
                if ($requirements['listing_required']) {
                    $checks[] = $user->growth_listing_created;
                }
                $isComplete = !in_array(false, $checks, true);
                break;

            default:
                $isComplete = $user->onboarding_completed;
        }

        // Automatically mark onboarding as completed if all requirements are met and auto-complete is enabled
        if ($isComplete && !$user->onboarding_completed && self::get(self::AUTO_COMPLETE_ONBOARDING, true)) {
            $user->update([
                'onboarding_completed' => true,
                'onboarding_completed_at' => now(),
            ]);
        }

        return $isComplete;
    }

    /**
     * Get the next onboarding step for a user
     */
    public static function getNextOnboardingStep(User $user): ?array
    {
        if (!$user->account_type) {
            return null;
        }

        if (self::hasUserCompletedOnboarding($user)) {
            return null;
        }

        $requirements = self::getOnboardingRequirements($user->account_type);

        switch ($user->account_type) {
            case 'buyer':
                $activationRequired = self::isActivationRequired('freelancer');
                $activationFee = self::getActivationFee('earner');
                if ($requirements['category_selection_required'] && !$user->buyer_onboarding_completed) {
                    return [
                        'step' => 'category_selection',
                        'route' => 'onboarding.buyer.categories',
                        'message' => 'Select your preferred categories to personalize your marketplace experience.',
                    ];
                }
    
                if ($activationRequired && (!$user->wallet || !$user->wallet->is_activated)) {

                    return [
                        'step' => 'activation',
                        'route' => 'wallet.activate',
                        'message' => 'Activate your wallet to continue as freelancer.',
                    ];
                }
                break;

            case 'earner':
                $activationRequired = self::isActivationRequired('earner');
                $activationFee = self::getActivationFee('earner');
                if ($activationRequired && (!$user->wallet || !$user->wallet->is_activated)) {
                    return [
                        'step' => 'activation',
                        'route' => 'wallet.activate',
                        'message' => 'Please activate your wallet to unlock worker tools.',
                        'fee' => $activationFee,
                    ];
                }
                break;

            case 'task_creator':
                    $activationRequired = self::isActivationRequired('task_creator');
                    if ($activationRequired && (!$user->wallet || !$user->wallet->is_activated)) {
                        return [
                            'step' => 'activation',
                            'route' => 'wallet.activate',
                            'message' => 'Activate your wallet to continue as task creator.',
                        ];
                    }
                if ($requirements['first_task_required'] && !$user->first_task_created) {
                    return [
                        'step' => 'first_task',
                        'route' => 'tasks.create.new',
                        'message' => 'Create your first task to complete your account setup.',
                    ];
                }
                break;

            case 'freelancer':
                $activationRequired = self::isActivationRequired('freelancer');
                $activationFee = self::getActivationFee('earner');
                if ($activationRequired && (!$user->wallet || !$user->wallet->is_activated)) {

                    return [
                        'step' => 'activation',
                        'route' => 'wallet.activate',
                        'message' => 'Activate your wallet to continue as freelancer.',
                    ];
                }
                if ($requirements['profile_required'] && !$user->freelancer_profile_completed) {
                    return [
                        'step' => 'profile',
                        'route' => 'professional-services.edit-profile',
                        'message' => 'Complete your freelancer profile before browsing professional services.',
                    ];
                }
                if ($requirements['service_required'] && !$user->freelancer_service_created) {
                    return [
                        'step' => 'service',
                        'route' => 'professional-services.create',
                        'message' => 'Create your first professional service to unlock marketplace browsing.',
                    ];
                }
                break;

            case 'digital_seller':
                $activationRequired = self::isActivationRequired('digital_seller');
                $activationFee = self::getActivationFee('earner');
                if ($activationRequired && (!$user->wallet || !$user->wallet->is_activated)) {
                    return [
                        'step' => 'activation',
                        'route' => 'wallet.activate',
                        'message' => 'Activate your wallet to continue as digital seller.',
                    ];
                }
                if ($requirements['product_required'] && !$user->digital_product_uploaded) {
                    return [
                        'step' => 'product',
                        'route' => 'digital-products.create',
                        'message' => 'Upload your first digital product to unlock the marketplace.',
                    ];
                }
                break;

            case 'growth_seller':
                $activationRequired = self::isActivationRequired('growth_seller');
                $activationFee = self::getActivationFee('earner');
                if ($activationRequired && (!$user->wallet || !$user->wallet->is_activated)) {
                    return [
                        'step' => 'activation',
                        'route' => 'wallet.activate',
                        'message' => 'Activate your wallet to continue as growth seller.',
                    ];
                }
                if ($requirements['listing_required'] && !$user->growth_listing_created) {
                    return [
                        'step' => 'listing',
                        'route' => 'growth.create',
                        'message' => 'Create your first growth listing to unlock the marketplace.',
                    ];
                }
                break;
        }

        return null;
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}