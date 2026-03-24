@extends('layouts.admin')

@section('title', 'SMTP / Email Settings')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">SMTP / Email Settings</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure email server and notification settings</p>
            </div>
            <div class="mt-4 md:mt-0 space-x-4">
                <a href="/admin" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Admin Home</a>
                <a href="{{ route('admin.settings') }}" class="text-indigo-600 hover:text-indigo-900">← Back to Settings</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-200 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @include('admin.settings._smtp_help')

        <form id="smtp-settings-form" action="{{ route('admin.settings.update', 'smtp') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- SMTP Toggle -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2 mr-3">
                            <i class="fas fa-envelope text-blue-600 dark:text-blue-300 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Email Service</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Enable or disable email notifications</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="smtp_enabled" value="true"
                                {{ (($settingsByKey['smtp_enabled'] ?? false) === true || ($settingsByKey['smtp_enabled'] ?? '') === 'true') ? 'checked' : '' }}
                                class="sr-only peer" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'disabled' }}>
                            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 dark:after:border-gray-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enabled</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- SMTP Configuration -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">SMTP Configuration</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Enter your SMTP server details</p>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="smtp_driver" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mail Driver</label>
                            <select name="smtp_driver" id="smtp_driver" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'disabled' }}>
                                <option value="smtp" {{ ($settingsByKey['smtp_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="turbosmtp" {{ ($settingsByKey['smtp_driver'] ?? '') === 'turbosmtp' ? 'selected' : '' }}>TurboSMTP</option>
                                <option value="mailgun" {{ ($settingsByKey['smtp_driver'] ?? '') === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="sendmail" {{ ($settingsByKey['smtp_driver'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="log" {{ ($settingsByKey['smtp_driver'] ?? '') === 'log' ? 'selected' : '' }}>Log (Debug)</option>
                            </select>
                        </div>
                        <div>
                            <label for="smtp_host" class="block text-sm font-medium text-gray-700 dark:text-gray-200">SMTP Host</label>
                            <input type="text" name="smtp_host" id="smtp_host" value="{{ old('smtp_host', $settingsByKey['smtp_host'] ?? '') }}" placeholder="smtp.example.com" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'readonly' }}>
                        </div>
                        <div>
                            <label for="smtp_port" class="block text-sm font-medium text-gray-700 dark:text-gray-200">SMTP Port</label>
                            <input type="number" name="smtp_port" id="smtp_port" value="{{ old('smtp_port', $settingsByKey['smtp_port'] ?? '587') }}" placeholder="587" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'readonly' }}>
                        </div>
                        <div>
                            <label for="smtp_encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Encryption Type</label>
                            <select name="smtp_encryption" id="smtp_encryption" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'disabled' }}>
                                <option value="tls" {{ ($settingsByKey['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($settingsByKey['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ ($settingsByKey['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMTP Credentials -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">SMTP Credentials</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Your email account credentials (stored encrypted)</p>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="smtp_username" class="block text-sm font-medium text-gray-700 dark:text-gray-200">SMTP Username</label>
                            <input type="text" name="smtp_username" id="smtp_username" value="{{ old('smtp_username', $settingsByKey['smtp_username'] ?? '') }}" placeholder="your-email@example.com" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'readonly' }}>
                        </div>
                        <div>
                            <label for="smtp_password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">SMTP Password</label>
                            <div class="flex items-center">
                                <input type="password" name="smtp_password" id="smtp_password" value="" placeholder="Leave blank to keep existing password" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'readonly' }}>
                                @if(!empty($settingsByKey['smtp_password']))
                                    <span class="ml-3 text-sm text-gray-500 dark:text-gray-300">(existing)</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to keep existing password</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email From Address -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">From Address</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">The sender information for outgoing emails</p>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="smtp_from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">From Email Address</label>
                            <input type="email" name="smtp_from_email" id="smtp_from_email" value="{{ old('smtp_from_email', $settingsByKey['smtp_from_email'] ?? '') }}" placeholder="noreply@swiftkudi.com" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'readonly' }}>
                        </div>
                        <div>
                            <label for="smtp_from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">From Name</label>
                            <input type="text" name="smtp_from_name" id="smtp_from_name" value="{{ old('smtp_from_name', $settingsByKey['smtp_from_name'] ?? 'SwiftKudi') }}" placeholder="EarnDesk" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" {{ auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT') ? '' : 'readonly' }}>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Email Section -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Test Email</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Send a test email to verify your SMTP configuration</p>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label for="test_email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Test Email Address</label>
                            <input type="email" id="test_email" name="test_email" placeholder="admin@swiftkudi.com" class="mt-1 block w-full text-base py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        @if(auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT'))
                        <button id="send-test" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-paper-plane mr-2"></i> Send Test Email
                        </button>
                        @else
                        <div class="text-sm text-gray-500 dark:text-gray-400">You do not have permission to send test emails.</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                @if(auth()->user()->hasPermission('\App\\Models\\AdminRole::PERMISSION_SETTINGS_EDIT'))
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i> Save SMTP Settings
                </button>
                @else
                <div class="text-sm text-gray-500 dark:text-gray-400">You do not have permission to edit settings.</div>
                @endif
            </div>
        </form>

        <form id="test-email-form" action="{{ route('admin.settings.test-smtp') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="test_email" id="test_email_hidden" value="">
        </form>

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const driverEl = document.getElementById('smtp_driver');
        const hostEl = document.getElementById('smtp_host');
        const portEl = document.getElementById('smtp_port');
        const encryptionEl = document.getElementById('smtp_encryption');
        const fromEmailEl = document.getElementById('smtp_from_email');
        const fromNameEl = document.getElementById('smtp_from_name');
        const turboDefaults = {
            host: 'pro.turbo-smtp.com',
            port: '587',
            encryption: 'tls',
            fromEmail: '',
            fromName: 'SwiftKudi'
        };

        const isBlank = (value) => !value || value.trim() === '';

        function applyTurboDefaults() {
            if (!driverEl || driverEl.value !== 'turbosmtp') {
                return;
            }

            if (hostEl && isBlank(hostEl.value)) {
                hostEl.value = turboDefaults.host;
            }

            if (portEl && isBlank(portEl.value)) {
                portEl.value = turboDefaults.port;
            }

            if (encryptionEl && isBlank(encryptionEl.value)) {
                encryptionEl.value = turboDefaults.encryption;
            }

            if (fromEmailEl && isBlank(fromEmailEl.value) && turboDefaults.fromEmail) {
                fromEmailEl.value = turboDefaults.fromEmail;
            }

            if (fromNameEl && isBlank(fromNameEl.value) && turboDefaults.fromName) {
                fromNameEl.value = turboDefaults.fromName;
            }
        }

        if (driverEl) {
            driverEl.addEventListener('change', applyTurboDefaults);
            applyTurboDefaults();
        }

        const sendBtn = document.getElementById('send-test');
        if (!sendBtn) return;

        sendBtn.addEventListener('click', function() {
            const testEmail = document.getElementById('test_email').value || document.getElementById('smtp_from_email').value;
            if (!testEmail) {
                alert('Please provide a test email address.');
                return;
            }

            document.getElementById('test_email_hidden').value = testEmail;
            document.getElementById('test-email-form').submit();
        });
    });
</script>
    @endpush

@endsection
