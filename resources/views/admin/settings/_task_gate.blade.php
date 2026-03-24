@extends('layouts.admin')

@section('title', 'Task Creation Gate Settings')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Task Creation Gate Settings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Configure mandatory task creation requirements for new users before they can earn.
            </p>
            <div class="mt-3">
                @php
                    $taskGateEnabled = \App\Models\SystemSetting::isMandatoryTaskCreationEnabled();
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $taskGateEnabled ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300' }}">
                    <i class="fas {{ $taskGateEnabled ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1.5"></i>
                    {{ $taskGateEnabled ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
        </div>

        <form action="{{ route('admin.settings.update', 'task-gate') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Enable/Disable Gate -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Enable Mandatory Task Creation</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            When enabled, new users must create a task with minimum budget before they can earn from tasks.
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="mandatory_task_creation_enabled" class="sr-only peer"
                                {{ \App\Models\SystemSetting::get('mandatory_task_creation_enabled', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Minimum Required Budget -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Minimum Required Budget</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        The minimum total task budget a user must create before unlocking earning capabilities.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="minimum_required_budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Budget Amount (₦)
                        </label>
                        <input type="number" id="minimum_required_budget" name="minimum_required_budget"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                               value="{{ \App\Models\SystemSetting::get('minimum_required_budget', 1000) }}"
                               min="100" step="100">
                    </div>

                    <div>
                        <label for="mandatory_budget_currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Currency
                        </label>
                        <select id="mandatory_budget_currency" name="mandatory_budget_currency"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                            <option value="NGN" {{ \App\Models\SystemSetting::get('mandatory_budget_currency', 'NGN') === 'NGN' ? 'selected' : '' }}>NGN (Naira)</option>
                            <option value="USD" {{ \App\Models\SystemSetting::get('mandatory_budget_currency', 'NGN') === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                            <option value="EUR" {{ \App\Models\SystemSetting::get('mandatory_budget_currency', 'NGN') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                        </select>
                    </div>
                </div>

                <!-- Quick preset buttons -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" onclick="setBudget(1000)"
                            class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        ₦1,000 (Minimum)
                    </button>
                    <button type="button" onclick="setBudget(2500)"
                            class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        ₦2,500 (Recommended)
                    </button>
                    <button type="button" onclick="setBudget(5000)"
                            class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        ₦5,000 (Standard)
                    </button>
                    <button type="button" onclick="setBudget(10000)"
                            class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        ₦10,000 (Premium)
                    </button>
                </div>
            </div>

            <!-- Platform Stats Configuration -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Platform Stats Display</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Configure the stats shown on the start-your-journey page for motivation.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="active_workers_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Active Workers Count
                        </label>
                        <input type="number" id="active_workers_count" name="active_workers_count"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                               value="{{ \App\Models\SystemSetting::get('active_workers_count', 1250) }}"
                               min="0">
                    </div>

                    <div>
                        <label for="total_paid_out" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Total Paid Out (₦)
                        </label>
                        <input type="number" id="total_paid_out" name="total_paid_out"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                               value="{{ \App\Models\SystemSetting::get('total_paid_out', 4500000) }}"
                               min="0">
                    </div>

                    <div>
                        <label for="success_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Success Rate (%)
                        </label>
                        <input type="number" id="success_rate" name="success_rate"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                               value="{{ \App\Models\SystemSetting::get('success_rate', 98) }}"
                               min="0" max="100">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit"
                        class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i>
                    Save Settings
                </button>
            </div>
        </form>

        <!-- Current Stats Preview -->
        <div class="mt-8 bg-gradient-to-r from-primary-50 to-purple-50 dark:from-primary-500/10 dark:to-purple-500/10 rounded-lg p-6 border border-primary-200 dark:border-primary-500/30">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current User Progress Preview</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                This is how the start-your-journey page will appear to new users who haven't completed the mandatory task creation requirement.
            </p>
            <div class="flex items-center gap-4 text-sm">
                <span class="px-3 py-1 bg-primary-100 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300 rounded-full">
                    <i class="fas fa-user-check mr-1"></i>
                    {{ \App\Models\SystemSetting::get('active_workers_count', 1250) }}+ Active Workers
                </span>
                <span class="px-3 py-1 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300 rounded-full">
                    <i class="fas fa-naira-sign mr-1"></i>
                    ₦{{ number_format(\App\Models\SystemSetting::get('total_paid_out', 4500000) / 1000000, 1) }}M+ Paid Out
                </span>
                <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-300 rounded-full">
                    <i class="fas fa-chart-line mr-1"></i>
                    {{ \App\Models\SystemSetting::get('success_rate', 98) }}% Success Rate
                </span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function setBudget(amount) {
        document.getElementById('minimum_required_budget').value = amount;
    }
</script>
@endpush
@endsection
