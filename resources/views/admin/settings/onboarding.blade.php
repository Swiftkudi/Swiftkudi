<?php
// Helper function to properly check checkbox state
function isChecked($value) {
    if (is_bool($value)) {
        return $value;
    }
    if (is_string($value)) {
        return in_array(strtolower($value), ['true', '1', 'on', 'yes']);
    }
    return (bool) $value;
}
?>

@extends('layouts.admin')

@section('title', 'Onboarding Settings')

@section('content')
<div class="py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Onboarding Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure role-based onboarding flows, requirements, and automatic progression</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Settings
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 dark:bg-green-500/20 border border-green-400 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.settings.onboarding.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- General Onboarding Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">General Onboarding Settings</h2>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="onboarding_enabled" value="1" id="onboarding_enabled"
                                   {{ isChecked($settings['onboarding_enabled']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="onboarding_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Onboarding System</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Globally enable/disable the onboarding system</p>
                            </div>
                        </div>

                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="auto_complete_onboarding" value="1" id="auto_complete_onboarding"
                                   {{ isChecked($settings['auto_complete_onboarding'] ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="auto_complete_onboarding" class="text-sm font-medium text-gray-900 dark:text-gray-100">Auto-Complete Onboarding</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Automatically mark onboarding complete when requirements are met</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buyer Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Buyer Onboarding Settings</h2>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="buyer_category_selection_required" value="1" id="buyer_category_selection_required"
                                   {{ isChecked($settings['buyer_category_selection_required']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="buyer_category_selection_required" class="text-sm font-medium text-gray-900 dark:text-gray-100">Category Selection Required</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Require buyers to select categories</p>
                            </div>
                        </div>

                        <div>
                            <label for="buyer_min_categories" class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Minimum Categories</label>
                            <input type="number" name="buyer_min_categories" id="buyer_min_categories"
                                   value="{{ $settings['buyer_min_categories'] }}" min="1" max="50"
                                   class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimum categories buyers must select</p>
                        </div>

                        <div>
                            <label for="buyer_max_categories" class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Maximum Categories</label>
                            <input type="number" name="buyer_max_categories" id="buyer_max_categories"
                                   value="{{ $settings['buyer_max_categories'] }}" min="1" max="50"
                                   class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum categories buyers can select</p>
                        </div>

                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="buyer_default_to_all_categories" value="1" id="buyer_default_to_all_categories"
                                   {{ isChecked($settings['buyer_default_to_all_categories']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="buyer_default_to_all_categories" class="text-sm font-medium text-gray-900 dark:text-gray-100">Default to All Categories</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Auto-select all categories for new buyers</p>
                            </div>
                        </div>

                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="buyer_allow_category_updates" value="1" id="buyer_allow_category_updates"
                                   {{ isChecked($settings['buyer_allow_category_updates']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="buyer_allow_category_updates" class="text-sm font-medium text-gray-900 dark:text-gray-100">Allow Category Updates</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Let buyers change their categories later</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earner Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Earner Onboarding Settings</h2>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="earner_onboarding_enabled" value="1" id="earner_onboarding_enabled"
                                   {{ isChecked($settings['earner_onboarding_enabled']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="earner_onboarding_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Earner Onboarding</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enable onboarding for earners</p>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        <i class="fas fa-info-circle mr-1"></i>
                        Activation settings are now configured in the "Account Type Activation Fees" section below.
                    </p>
                </div>
            </div>

            <!-- Task Creator Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Task Creator Onboarding Settings</h2>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="task_creator_onboarding_enabled" value="1" id="task_creator_onboarding_enabled"
                                   {{ isChecked($settings['task_creator_onboarding_enabled']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="task_creator_onboarding_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Task Creator Onboarding</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enable onboarding for task creators</p>
                            </div>
                        </div>

                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="task_creator_first_task_required" value="1" id="task_creator_first_task_required"
                                   {{ isChecked($settings['task_creator_first_task_required']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="task_creator_first_task_required" class="text-sm font-medium text-gray-900 dark:text-gray-100">First Task Required</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Require creating first task to complete onboarding</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Freelancer Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Freelancer Onboarding Settings</h2>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="freelancer_onboarding_enabled" value="1" id="freelancer_onboarding_enabled"
                                   {{ isChecked($settings['freelancer_onboarding_enabled']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="freelancer_onboarding_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Freelancer Onboarding</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enable onboarding for freelancers</p>
                            </div>
                        </div>

                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="freelancer_profile_required" value="1" id="freelancer_profile_required"
                                   {{ isChecked($settings['freelancer_profile_required']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="freelancer_profile_required" class="text-sm font-medium text-gray-900 dark:text-gray-100">Profile Completion Required</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Require profile completion</p>
                            </div>
                        </div>

                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="freelancer_service_required" value="1" id="freelancer_service_required"
                                   {{ isChecked($settings['freelancer_service_required']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="freelancer_service_required" class="text-sm font-medium text-gray-900 dark:text-gray-100">First Service Required</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Require creating first service</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Digital Seller Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Digital Seller Onboarding Settings</h2>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="digital_seller_onboarding_enabled" value="1" id="digital_seller_onboarding_enabled"
                                   {{ isChecked($settings['digital_seller_onboarding_enabled']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="digital_seller_onboarding_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Digital Seller Onboarding</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enable onboarding for digital sellers</p>
                            </div>
                        </div>

                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="digital_product_required" value="1" id="digital_product_required"
                                   {{ isChecked($settings['digital_product_required']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="digital_product_required" class="text-sm font-medium text-gray-900 dark:text-gray-100">First Product Required</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Require uploading first product</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growth Seller Settings -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Growth Seller Onboarding Settings</h2>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="growth_seller_onboarding_enabled" value="1" id="growth_seller_onboarding_enabled"
                                   {{ isChecked($settings['growth_seller_onboarding_enabled']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="growth_seller_onboarding_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">Enable Growth Seller Onboarding</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enable onboarding for growth sellers</p>
                            </div>
                        </div>

                        <div class="flex items-center p-4 rounded-xl bg-gray-50 dark:bg-dark-800">
                            <input type="checkbox" name="growth_listing_required" value="1" id="growth_listing_required"
                                   {{ isChecked($settings['growth_listing_required']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <div class="ml-4">
                                <label for="growth_listing_required" class="text-sm font-medium text-gray-900 dark:text-gray-100">First Listing Required</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Require creating first growth listing</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Type Activation Configuration -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Account Type Activation Fees</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure activation fees and requirements per account type</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300">
                        <i class="fas fa-money-bill-wave mr-1.5"></i>Centralized
                    </span>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($accountConfigs as $type => $config)
                        <div class="border border-gray-200 dark:border-dark-700 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                    <i class="fas fa-user-{{ $type === 'earner' ? 'plus' : ($type === 'buyer' ? 'circle' : 'tag') }} mr-1 text-indigo-600"></i>
                                    {{ $config['label'] }}
                                </h3>
                                <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="account_configs[{{ $type }}][enabled]" id="acc_enabled_{{ $type }}" 
                                           {{ $config['enabled'] ? 'checked' : '' }}
                                           class="absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer toggle-checkbox-sm">
                                    <label for="acc_enabled_{{ $type }}" class="block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer toggle-label-sm"></label>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <label for="acc_required_{{ $type }}" class="text-sm text-gray-600 dark:text-gray-400">Activation Required</label>
                                    <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="account_configs[{{ $type }}][activation_required]" id="acc_required_{{ $type }}" 
                                               {{ $config['activation_required'] ? 'checked' : '' }}
                                               class="absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer toggle-checkbox-sm">
                                        <label for="acc_required_{{ $type }}" class="block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer toggle-label-sm"></label>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="acc_fee_{{ $type }}" class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Fee (₦)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-gray-400 text-sm">₦</span>
                                        <input type="number" name="account_configs[{{ $type }}][activation_fee]" id="acc_fee_{{ $type }}" 
                                               value="{{ $config['activation_fee'] }}" min="0" step="0.01"
                                               class="w-full pl-6 pr-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-dark-600 dark:bg-dark-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-dark-700">
                <div class="flex space-x-3">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-save mr-2"></i>Save Settings
                    </button>

                    <a href="{{ route('admin.settings.onboarding.reset') }}"
                       onclick="return confirm('Are you sure you want to reset all onboarding settings to defaults?')"
                       class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-dark-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-dark-800 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-dark-700 transition-colors">
                        <i class="fas fa-redo mr-2"></i>Reset to Defaults
                    </a>
                </div>

                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Changes take effect immediately
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Add some client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const minCategories = document.getElementById('buyer_min_categories');
    const maxCategories = document.getElementById('buyer_max_categories');

    function validateCategoryLimits() {
        const min = parseInt(minCategories.value);
        const max = parseInt(maxCategories.value);

        if (min > max) {
            maxCategories.setCustomValidity('Maximum categories must be greater than or equal to minimum categories');
        } else {
            maxCategories.setCustomValidity('');
        }
    }

    minCategories.addEventListener('input', validateCategoryLimits);
    maxCategories.addEventListener('input', validateCategoryLimits);
});
</script>

<style>
/* Toggle switch styles for account config section */
.toggle-checkbox-sm:checked {
    right: 0;
    border-color: #4f46e5;
}
.toggle-checkbox-sm:checked + .toggle-label-sm {
    background-color: #4f46e5;
}
.toggle-checkbox-sm {
    transition: all 0.2s;
    right: 0;
    border: 2px solid #d1d5db;
}
.toggle-label-sm {
    transition: all 0.2s;
}
</style>
@endsection