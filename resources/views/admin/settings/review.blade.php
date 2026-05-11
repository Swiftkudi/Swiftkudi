@extends('layouts.admin')

@section('title', 'Review & Rating Settings')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-violet-600 to-purple-600 bg-clip-text text-transparent">Review & Rating Settings</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Configure review rules, moderation, and visibility for services and products</p>
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

        <form action="{{ route('admin.settings.update', 'review') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Review Module Enable/Disable -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-star text-violet-600 dark:text-violet-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Review Modules</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Enable or disable reviews for different marketplace sections</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="review_services_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Professional Services Reviews
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Allow reviews on professional services</p>
                        </div>
                        <input type="checkbox" id="review_services_enabled" name="review_services_enabled" value="true"
                            {{ (data_get($settingsByKey, 'review_services_enabled', true) === 'true' || data_get($settingsByKey, 'review_services_enabled', true)) ? 'checked' : '' }}
                            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label for="review_growth_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Growth Services Reviews
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Allow reviews on growth listings</p>
                        </div>
                        <input type="checkbox" id="review_growth_enabled" name="review_growth_enabled" value="true"
                            {{ (data_get($settingsByKey, 'review_growth_enabled', true) === 'true' || data_get($settingsByKey, 'review_growth_enabled', true)) ? 'checked' : '' }}
                            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label for="review_digital_enabled" class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Digital Product Reviews
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Allow reviews on digital products</p>
                        </div>
                        <input type="checkbox" id="review_digital_enabled" name="review_digital_enabled" value="true"
                            {{ (data_get($settingsByKey, 'review_digital_enabled', true) === 'true' || data_get($settingsByKey, 'review_digital_enabled', true)) ? 'checked' : '' }}
                            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                    </div>
                </div>
            </div>

            <!-- Rating Scale -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-scale-balanced text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rating Scale</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure minimum and maximum rating values</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="review_min_rating" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Minimum Rating
                            </label>
                            <input type="number" name="review_min_rating" id="review_min_rating"
                                value="{{ old('review_min_rating', $settingsByKey['review_min_rating'] ?? 1) }}"
                                min="0" max="5" step="1"
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                        </div>

                        <div>
                            <label for="review_max_rating" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Maximum Rating
                            </label>
                            <input type="number" name="review_max_rating" id="review_max_rating"
                                value="{{ old('review_max_rating', $settingsByKey['review_max_rating'] ?? 5) }}"
                                min="1" max="10" step="1"
                                class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Rules -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-gavel text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Review Rules</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure who can leave reviews and when</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="review_require_purchase" name="review_require_purchase" value="true"
                            {{ (data_get($settingsByKey, 'review_require_purchase', true) === 'true' || data_get($settingsByKey, 'review_require_purchase', true)) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                        <label for="review_require_purchase" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                            Require Purchase
                        </label>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">(Only buyers who completed orders can review)</span>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="review_allow_anonymous" name="review_allow_anonymous" value="true"
                            {{ (data_get($settingsByKey, 'review_allow_anonymous', false) === 'true' || data_get($settingsByKey, 'review_allow_anonymous', false)) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                        <label for="review_allow_anonymous" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                            Allow Anonymous Reviews
                        </label>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">(Users can review without showing their identity)</span>
                    </div>

                    <div>
                        <label for="review_edit_window_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 mt-4">
                            Review Edit Window (Hours)
                        </label>
                        <input type="number" name="review_edit_window_hours" id="review_edit_window_hours"
                            value="{{ old('review_edit_window_hours', $settingsByKey['review_edit_window_hours'] ?? 24) }}"
                            min="0" max="168" step="1"
                            class="w-full rounded-xl border-gray-200 dark:border-dark-700 dark:bg-dark-800 focus:border-indigo-500 focus:ring-indigo-500 dark:text-white">
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Time window for users to edit their reviews (0 to disable)</p>
                    </div>
                </div>
            </div>

            <!-- Moderation -->
            <div class="bg-white dark:bg-dark-900 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-dark-950/50 border border-gray-100 dark:border-dark-700 mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center mr-3">
                            <i class="fas fa-user-shield text-orange-600 dark:text-orange-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Moderation</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Control review visibility and moderation</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="review_moderation_enabled" name="review_moderation_enabled" value="true"
                            {{ (data_get($settingsByKey, 'review_moderation_enabled', false) === 'true' || data_get($settingsByKey, 'review_moderation_enabled', false)) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                        <label for="review_moderation_enabled" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                            Enable Moderation
                        </label>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">(Reviews require admin approval before publishing)</span>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="review_auto_publish" name="review_auto_publish" value="true"
                            {{ (data_get($settingsByKey, 'review_auto_publish', true) === 'true' || data_get($settingsByKey, 'review_auto_publish', true)) ? 'checked' : '' }}
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-dark-600 rounded dark:bg-dark-800">
                        <label for="review_auto_publish" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                            Auto-Publish Reviews
                        </label>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">(Publish reviews immediately without moderation)</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold rounded-xl hover:from-violet-700 hover:to-purple-700 transition-all shadow-lg shadow-violet-500/30">
                    <i class="fas fa-save mr-2"></i>Save Review Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection