<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OnboardingSettingsService;
use App\Models\OnboardingAccountConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OnboardingSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->is_admin) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access admin settings.');
            }
            return $next($request);
        });
    }

    /**
     * Show onboarding settings page
     */
    public function index()
    {
        $settings = OnboardingSettingsService::getAllSettings();
        $accountConfigs = OnboardingAccountConfig::getAllConfigs();

        return view('admin.settings.onboarding', compact('settings', 'accountConfigs'));
    }

    /**
     * Update onboarding settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // General settings
            'onboarding_enabled' => 'boolean',
            'auto_complete_onboarding' => 'boolean',
            'buyer_onboarding_enabled' => 'boolean',

            // Buyer settings
            'buyer_category_selection_required' => 'boolean',
            'buyer_min_categories' => 'integer|min:1|max:50',
            'buyer_max_categories' => 'integer|min:1|max:50|gte:buyer_min_categories',
            'buyer_default_to_all_categories' => 'boolean',
            'buyer_allow_category_updates' => 'boolean',

            // Earner settings (onboarding enabled only - activation is now in account_configs)
            'earner_onboarding_enabled' => 'boolean',

            // Task creator settings
            'task_creator_onboarding_enabled' => 'boolean',
            'task_creator_first_task_required' => 'boolean',

            // Freelancer settings (onboarding enabled only - activation is now in account_configs)
            'freelancer_onboarding_enabled' => 'boolean',
            'freelancer_profile_required' => 'boolean',
            'freelancer_service_required' => 'boolean',

            // Digital seller settings (onboarding enabled only - activation is now in account_configs)
            'digital_seller_onboarding_enabled' => 'boolean',
            'digital_product_required' => 'boolean',

            // Growth seller settings (onboarding enabled only - activation is now in account_configs)
            'growth_seller_onboarding_enabled' => 'boolean',
            'growth_listing_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // List of all checkbox keys that should default to false when unchecked
            // Note: activation_required and activation_fee removed - now handled in account_configs
            $checkboxKeys = [
                'onboarding_enabled',
                'auto_complete_onboarding',
                'buyer_onboarding_enabled',
                'buyer_category_selection_required',
                'buyer_default_to_all_categories',
                'buyer_allow_category_updates',
                'earner_onboarding_enabled',
                'task_creator_onboarding_enabled',
                'task_creator_first_task_required',
                'freelancer_onboarding_enabled',
                'freelancer_profile_required',
                'freelancer_service_required',
                'digital_seller_onboarding_enabled',
                'digital_product_required',
                'growth_seller_onboarding_enabled',
                'growth_listing_required',
            ];

            // Get requested settings (only checkboxes that were sent)
            // Note: earner_activation_fee removed - now handled in account_configs
            $requestedSettings = $request->only(array_merge($checkboxKeys, [
                'buyer_min_categories',
                'buyer_max_categories',
            ]));

            // Set unchecked checkboxes to false - HTML checkboxes don't send value when unchecked
            foreach ($checkboxKeys as $key) {
                if (!isset($requestedSettings[$key]) || $requestedSettings[$key] === null) {
                    $requestedSettings[$key] = false;
                }
            }

            // Update all settings
            foreach ($requestedSettings as $key => $value) {
                // Convert string booleans to actual booleans
                if (is_string($value) && in_array($value, ['true', 'false', '1', '0', 'on'])) {
                    $value = in_array($value, ['true', '1', 'on'], true);
                }
                OnboardingSettingsService::set($key, $value);
            }

            // Handle account configs (centralized activation settings)
            if ($request->has('account_configs')) {
                $accountConfigs = $request->input('account_configs');
                foreach ($accountConfigs as $accountType => $config) {
                    OnboardingSettingsService::updateAccountConfig($accountType, [
                        'enabled' => isset($config['enabled']),
                        'activation_required' => isset($config['activation_required']),
                        'activation_fee' => floatval($config['activation_fee'] ?? 0),
                    ]);
                }
            }

            // Clear cache and get fresh settings to ensure UI reflects actual persisted state
            OnboardingSettingsService::clearCache();
            $freshSettings = OnboardingSettingsService::getAllSettings();

            return back()->with('success', 'Onboarding settings updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Reset onboarding settings to defaults
     */
    public function reset()
    {
        try {
            // Reset to default values by clearing cache and letting defaults load
            OnboardingSettingsService::clearCache();

            // Reset account configs to defaults
            OnboardingAccountConfig::resetToDefaults();

            return back()->with('success', 'Onboarding settings reset to defaults.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    /**
     * Get onboarding settings as JSON (for AJAX)
     */
    public function getSettings()
    {
        return response()->json([
            'success' => true,
            'settings' => OnboardingSettingsService::getAllSettings(),
            'accountConfigs' => OnboardingAccountConfig::getAllConfigs(),
        ]);
    }
}