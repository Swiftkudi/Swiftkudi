<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\AdminRole;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    protected $superAdminOnly = ['general', 'smtp', 'payment', 'security', 'cron', 'commission', 'currency', 'maintenance', 'audit', 'registration', 'task-gate'];

    private const TASK_GATE_DEFAULTS = [
        'mandatory_task_creation_enabled' => ['value' => true, 'type' => 'boolean'],
        'minimum_required_budget' => ['value' => 2500, 'type' => 'number'],
        'mandatory_budget_currency' => ['value' => 'NGN', 'type' => 'text'],
        'active_workers_count' => ['value' => 1250, 'type' => 'number'],
        'total_paid_out' => ['value' => 4500000, 'type' => 'number'],
        'success_rate' => ['value' => 98, 'type' => 'number'],
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access settings.');
            }

            $user = Auth::user();
            if (!$user instanceof User) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access settings.');
            }

            // Super-admin bypasses permission checks
            if ($user->isSuperAdmin()) {
                return $next($request);
            }

            // Require settings.view permission for non-super admins
            if (!$user->hasPermission(AdminRole::PERMISSION_SETTINGS_VIEW)) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access settings.');
            }

            return $next($request);
        });
    }

    /**
     * Main settings dashboard
     */
    public function index()
    {
        $groups = SystemSetting::GROUPS;
        $settingsCounts = [];

        foreach ($groups as $key => $label) {
            $settingsCounts[$key] = SystemSetting::where('group', $key)->count();
        }

        return view('admin.settings', compact('groups', 'settingsCounts'));
    }

    /**
     * Display settings for a specific group
     */
    public function group($group)
    {
        // Allow task-gate as a special custom group
        $allowedGroups = array_keys(SystemSetting::GROUPS);
        if (!in_array($group, $allowedGroups) && $group !== 'task-gate') {
            return redirect()->route('admin.settings')->with('error', 'Invalid settings group.');
        }

        // Check if user can view this settings group
        if (!$this->canAccessSettings($group)) {
            return redirect()->route('admin.index')
                ->with('error', 'You do not have permission to access these settings.');
        }

        // Special handling for task-gate group
        if ($group === 'task-gate') {
            $this->ensureTaskGateSettingsExist();
            $settings = SystemSetting::where('group', 'task-gate')->get();
            $settingsByKey = [];
            foreach ($settings as $s) {
                $settingsByKey[$s->key] = SystemSetting::get($s->key, $s->value);
            }
            return view('admin.settings._task_gate', compact('settings', 'settingsByKey', 'group'));
        }

        if ($group === 'registration') {
            $this->ensureRegistrationSettingsExist();
        }

        if ($group === 'notification') {
            $this->ensureNotificationSettingsExist();
        }

        if ($group === 'task-gate') {
            $this->ensureTaskGateSettingsExist();
        }

        $settings = SystemSetting::where('group', $group)->get();

        // Build settings by key using casted/decrypted values so views show usable values
        $settingsByKey = [];
        foreach ($settings as $s) {
            $settingsByKey[$s->key] = SystemSetting::get($s->key, $s->value);
        }

        return view("admin.settings.{$group}", compact('settings', 'settingsByKey', 'group'));
    }

    /**
     * Check if user can access settings group
     */
    protected function canAccessSettings(string $group): bool
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return false;
        }

        // Super admin can access all settings
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Non-super admins must have view permission; additionally block super-only groups
        if (in_array($group, $this->superAdminOnly)) {
            return false;
        }

        return $user->hasPermission(AdminRole::PERMISSION_SETTINGS_VIEW);
    }

    /**
     * Save settings for a specific group
     */
    public function update(Request $request, $group)
    {
        if (!array_key_exists($group, SystemSetting::GROUPS)) {
            return redirect()->route('admin.settings')->with('error', 'Invalid settings group.');
        }

        // Check if user can edit this settings group
        if (!$this->canEditSettings($group)) {
            return redirect()->route('admin.index')
                ->with('error', 'You do not have permission to edit these settings.');
        }

        if ($group === 'task-gate') {
            return $this->updateTaskGateSettings($request);
        }

        if ($group === 'registration') {
            $this->ensureRegistrationSettingsExist();
        }

        if ($group === 'notification') {
            $this->ensureNotificationSettingsExist();
        }

        $settings = SystemSetting::where('group', $group)->get();
        $rules = [];
        $messages = [];

        // Build validation rules based on setting types
        foreach ($settings as $setting) {
            $key = $setting->key;
            $rules[$key] = $this->getValidationRules($setting->type);

            if ($setting->type === 'number') {
                $messages[$key . '.numeric'] = "The {$key} must be a number.";
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Save settings
        foreach ($settings as $setting) {
            $key = $setting->key;

            // Handle boolean toggles
            if ($setting->type === 'boolean') {
                $value = $request->has($key) ? 'true' : 'false';
                SystemSetting::set($key, $value, $group, $setting->type);
                continue;
            }

            // For encrypted values: only update if provided (leave blank to keep existing)
            if ($setting->type === 'encrypted') {
                if ($request->filled($key)) {
                    $value = $request->input($key);
                    if ($group === 'payment') {
                        $value = $this->sanitizePaymentCredential($key, $value);
                    }
                    SystemSetting::set($key, $value, $group, 'encrypted');
                }
                // skip if not filled
                continue;
            }

            // Numbers
            if ($setting->type === 'number') {
                $value = $request->input($key);
                $value = is_null($value) ? null : (float) $value;
                SystemSetting::set($key, $value, $group, 'number');
                continue;
            }

            // Default: text, json, etc.
            $value = $request->input($key);
            SystemSetting::set($key, $value, $group, $setting->type);
        }

        // Special handling for certain groups
        if ($group === 'maintenance') {
            $this->handleMaintenanceMode($request);
        }

        // Special handling for currency settings - clear currency cache
        if ($group === 'currency') {
            $this->handleCurrencySettings($request);
        }

        // Prevent disabling all payment gateways (strict validation)
        if ($group === 'payment') {
            $this->validatePaymentGateways(true);
        }

        // Apply mail config immediately if smtp settings changed
        if ($group === 'smtp') {
            $this->applyMailConfigFromSettings();
        }

        return redirect()->back()->with('success', ucfirst($group) . ' settings saved successfully.');
    }

    /**
     * Ensure required registration settings rows exist.
     */
    protected function ensureRegistrationSettingsExist(): void
    {
        $defaults = [
            'registration_enabled' => ['value' => true, 'type' => 'boolean'],
            'email_verification_required' => ['value' => true, 'type' => 'boolean'],
            'admin_approval_required' => ['value' => false, 'type' => 'boolean'],
            'referral_enabled' => ['value' => true, 'type' => 'boolean'],
            'referral_bonus_amount' => ['value' => 500, 'type' => 'number'],
            'activation_fee' => ['value' => 1000, 'type' => 'number'],
            'referred_activation_discount' => ['value' => 0, 'type' => 'number'],
            'referred_activation_multiplier' => ['value' => 1.0, 'type' => 'number'],
            'compulsory_activation_fee' => ['value' => true, 'type' => 'boolean'],
        ];

        foreach ($defaults as $key => $meta) {
            if (!SystemSetting::keyExists($key)) {
                SystemSetting::set($key, $meta['value'], 'registration', $meta['type']);
            }
        }
    }

    /**
     * Ensure required task-gate settings rows exist.
     */
    protected function ensureTaskGateSettingsExist(): void
    {
        foreach (self::TASK_GATE_DEFAULTS as $key => $meta) {
            $setting = SystemSetting::where('key', $key)->first();
            if (!$setting) {
                SystemSetting::set($key, $meta['value'], 'task-gate', $meta['type']);
                continue;
            }

            if ($setting->group !== 'task-gate') {
                $currentValue = SystemSetting::get($key, $meta['value']);
                SystemSetting::set($key, $currentValue, 'task-gate', $setting->type ?: $meta['type']);
            }
        }
    }

    /**
     * Ensure required notification settings rows exist.
     */
    protected function ensureNotificationSettingsExist(): void
    {
        $defaults = [
            'notify_in_app_enabled' => ['value' => true, 'type' => 'boolean'],
            'notify_email_enabled' => ['value' => true, 'type' => 'boolean'],
            'notify_task_approval' => ['value' => true, 'type' => 'boolean'],
            'notify_task_rejection' => ['value' => true, 'type' => 'boolean'],
            'notify_task_bundle' => ['value' => true, 'type' => 'boolean'],
            'notify_referral_bonus' => ['value' => true, 'type' => 'boolean'],
            'notify_withdrawal' => ['value' => true, 'type' => 'boolean'],
            'notify_task_created' => ['value' => true, 'type' => 'boolean'],
            'notify_service_orders' => ['value' => true, 'type' => 'boolean'],
            'notify_growth_orders' => ['value' => true, 'type' => 'boolean'],
            'notify_product_orders' => ['value' => true, 'type' => 'boolean'],
            'notify_chat_messages' => ['value' => true, 'type' => 'boolean'],
            'notify_admin_all_activity' => ['value' => true, 'type' => 'boolean'],
            'notify_large_withdrawal' => ['value' => true, 'type' => 'boolean'],
            'admin_fraud_alerts' => ['value' => true, 'type' => 'boolean'],
            'large_withdrawal_threshold' => ['value' => 50000, 'type' => 'number'],
            'notif_welcome_subject' => ['value' => 'Welcome to {{site_name}}!', 'type' => 'text'],
            'notif_welcome_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_welcome_body' => ['value' => "Hello {{user_name}},\n\nWelcome to {{site_name}}! We're excited to have you on board.\n\nYour referral code: {{referral_code}}\n\nGet started by completing your profile and exploring available tasks.", 'type' => 'text'],
            'notif_task_approved_subject' => ['value' => 'Your Task Has Been Approved!', 'type' => 'text'],
            'notif_task_approved_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_task_approved_body' => ['value' => "Hello {{user_name}},\n\nGreat news! Your task \"{{task_title}}\" has been approved.\n\nEarnings: {{earnings}}\n\nKeep up the great work!", 'type' => 'text'],
            'notif_task_rejected_subject' => ['value' => 'Task Update: Not Approved', 'type' => 'text'],
            'notif_task_rejected_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_task_rejected_body' => ['value' => "Hello {{user_name}},\n\nYour task \"{{task_title}}\" was not approved.\n\nReason: {{rejection_reason}}\n\nPlease review the feedback and resubmit if needed.", 'type' => 'text'],
            'notif_earnings_unlocked_subject' => ['value' => 'Your Earnings Are Now Available!', 'type' => 'text'],
            'notif_earnings_unlocked_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_earnings_unlocked_body' => ['value' => "Hello {{user_name}},\n\nGreat news! Your earnings of {{amount}} have been unlocked and are now available in your wallet.\n\nYou can withdraw these funds or use them for premium tasks.\n\nCurrent Balance: {{wallet_balance}}", 'type' => 'text'],
            'notif_activation_reminder_subject' => ['value' => 'Reminder: Complete Your Wallet Activation', 'type' => 'text'],
            'notif_activation_reminder_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_activation_reminder_body' => ['value' => "Hello {{user_name}},\n\nThis is a friendly reminder to complete your wallet activation.\n\nActivate now to unlock your earnings and start withdrawing!\n\nActivation Fee: {{activation_fee}}", 'type' => 'text'],
            'notif_password_reset_subject' => ['value' => 'Reset Your Password', 'type' => 'text'],
            'notif_password_reset_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_password_reset_body' => ['value' => "Hello {{user_name}},\n\nYou requested a password reset. Click the link below to reset your password:\n\n{{reset_link}}\n\nThis link expires in 60 minutes.", 'type' => 'text'],
            'notif_email_verify_subject' => ['value' => 'Verify Your Email Address', 'type' => 'text'],
            'notif_email_verify_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_email_verify_body' => ['value' => "Hello {{user_name}},\n\nPlease verify your email address by clicking the link below:\n\n{{verify_link}}\n\nIf you did not create an account, please ignore this email.", 'type' => 'text'],
            'notif_withdrawal_subject' => ['value' => 'Withdrawal Processed Successfully', 'type' => 'text'],
            'notif_withdrawal_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_withdrawal_body' => ['value' => "Hello {{user_name}},\n\nYour withdrawal of {{amount}} has been processed successfully.\n\nWithdrawal Method: {{method}}\n\nAmount Received: {{net_amount}}", 'type' => 'text'],
            'notif_referral_bonus_subject' => ['value' => 'You Earned a Referral Bonus!', 'type' => 'text'],
            'notif_referral_bonus_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_referral_bonus_body' => ['value' => "Hello {{user_name}},\n\nCongratulations! You earned a referral bonus of {{bonus_amount}}!\n\nYour referral {{referred_user}} has completed their first task.\n\nShare your referral code to earn more: {{referral_code}}", 'type' => 'text'],
            'notif_task_created_subject' => ['value' => 'Your Task Has Been Created Successfully!', 'type' => 'text'],
            'notif_task_created_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_task_created_body' => ['value' => "Hello {{user_name}},\n\nYour task \"{{task_title}}\" has been created successfully and is now being processed.\n\nWorkers will start picking it up shortly. You will be notified as submissions come in.\n\nView your task: {{task_url}}", 'type' => 'text'],
            'notif_task_bundle_subject' => ['value' => 'New Task Available - Earn Now!', 'type' => 'text'],
            'notif_task_bundle_from_name' => ['value' => config('app.name', 'SwiftKudi'), 'type' => 'text'],
            'notif_task_bundle_body' => ['value' => "Hello {{user_name}},\n\nA new task bundle is now available on {{site_name}}: \"{{task_title}}\".\n\nLog in now to complete it and earn your reward.\n\nView task: {{task_url}}", 'type' => 'text'],
        ];

        foreach ($defaults as $key => $meta) {
            if (!SystemSetting::keyExists($key)) {
                SystemSetting::set($key, $meta['value'], 'notification', $meta['type']);
            }
        }
    }

    /**
     * Persist task-gate settings explicitly so toggles always apply.
     */
    protected function updateTaskGateSettings(Request $request)
    {
        $this->ensureTaskGateSettingsExist();

        $validated = $request->validate([
            'minimum_required_budget' => ['nullable', 'numeric', 'min:0'],
            'mandatory_budget_currency' => ['nullable', 'string', 'max:10'],
            'active_workers_count' => ['nullable', 'numeric', 'min:0'],
            'total_paid_out' => ['nullable', 'numeric', 'min:0'],
            'success_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        SystemSetting::set(
            'mandatory_task_creation_enabled',
            $request->has('mandatory_task_creation_enabled') ? 'true' : 'false',
            'task-gate',
            'boolean'
        );

        // Keep legacy key in sync for any older code paths still reading it.
        SystemSetting::set(
            'compulsory_task_creation_before_earning',
            $request->has('mandatory_task_creation_enabled') ? 'true' : 'false',
            'general',
            'boolean'
        );

        SystemSetting::set('minimum_required_budget', $validated['minimum_required_budget'] ?? 2500, 'task-gate', 'number');
        SystemSetting::set('mandatory_budget_currency', $validated['mandatory_budget_currency'] ?? 'NGN', 'task-gate', 'text');
        SystemSetting::set('active_workers_count', $validated['active_workers_count'] ?? 1250, 'task-gate', 'number');
        SystemSetting::set('total_paid_out', $validated['total_paid_out'] ?? 4500000, 'task-gate', 'number');
        SystemSetting::set('success_rate', $validated['success_rate'] ?? 98, 'task-gate', 'number');

        return redirect()->back()->with('success', 'Task-gate settings saved successfully.');
    }

    /**
     * Normalize payment credential values before persisting.
     */
    protected function sanitizePaymentCredential(string $key, $value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $normalized = trim($value);
        $isSecret = substr($key, -11) === '_secret_key' || substr($key, -15) === '_webhook_secret';

        if ($isSecret && stripos($normalized, 'Bearer ') === 0) {
            $normalized = trim(substr($normalized, 7));
        }

        return $normalized;
    }

    /**
     * Get validation rules based on setting type
     */
    protected function getValidationRules(string $type): array
    {
        switch ($type) {
            case 'boolean':
                return ['nullable'];
            case 'number':
                return ['required', 'numeric', 'min:0'];
            case 'email':
                return ['nullable', 'email'];
            case 'url':
                return ['nullable', 'url'];
            case 'encrypted':
                return ['nullable', 'string', 'max:2000'];
            default:
                return ['nullable', 'string', 'max:5000'];
        }
    }

    /**
     * Handle maintenance mode settings
     */
    protected function handleMaintenanceMode(Request $request): void
    {
        $enabled = $request->has('maintenance_mode_enabled');

        if ($enabled) {
            $message = $request->input('maintenance_message', 'System is under maintenance.');
            SystemSetting::enableMaintenanceMode($message);
        } else {
            SystemSetting::disableMaintenanceMode();
        }
    }

    /**
     * Handle currency settings - ensure currency changes are reflected immediately
     */
    protected function handleCurrencySettings(Request $request): void
    {
        // Clear any cached currency data
        Cache::forget('default_currency');
        Cache::forget('currency_default');
        
        // Clear all currency-related cache tags if using cache tags
        try {
            Cache::flush();
        } catch (\Exception $e) {
            // If flush fails, individual keys should still be cleared above
        }
    }

    /**
     * Validate that at least one payment gateway is enabled and adjust defaults
     */
    protected function validatePaymentGateways(bool $throwOnFailure = false): void
    {
        $paystack = SystemSetting::getBool('paystack_enabled');
        $kora = SystemSetting::getBool('kora_enabled');
        $stripe = SystemSetting::getBool('stripe_enabled');

        if (!$paystack && !$kora && !$stripe) {
            if ($throwOnFailure) {
                throw ValidationException::withMessages(['payment' => 'At least one payment gateway must be enabled.']);
            }

            // fallback behavior
            SystemSetting::set('paystack_enabled', true, 'payment', 'boolean');
            return;
        }

        // Ensure default gateway per currency is enabled; if not, switch to an enabled gateway
        $defaults = [
            'default_gateway_ngn' => 'paystack',
            'default_gateway_usd' => 'stripe',
            'default_gateway_usdt' => 'stripe',
        ];

        foreach ($defaults as $key => $default) {
            $current = SystemSetting::get($key, $default);
            $currentEnabled = SystemSetting::getBool($current . '_enabled');

            if (!$currentEnabled) {
                // find first enabled gateway
                if ($paystack) {
                    SystemSetting::set($key, 'paystack', 'payment', 'text');
                } elseif ($kora) {
                    SystemSetting::set($key, 'kora', 'payment', 'text');
                } elseif ($stripe) {
                    SystemSetting::set($key, 'stripe', 'payment', 'text');
                }
            }
        }
    }

    /**
     * Check if user can edit settings group
     */
    protected function canEditSettings(string $group): bool
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return false;
        }
        
        // Super admin can edit all settings
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Check if group requires super admin
        if (in_array($group, $this->superAdminOnly)) {
            return false;
        }

        return $user->hasPermission(AdminRole::PERMISSION_SETTINGS_EDIT);
    }

    /**
     * Send test email
     */
    public function testSmtp(Request $request)
    {
        $email = $request->input('test_email');

        if (!$email) {
            return redirect()->back()->with('error', 'Please provide a test email address.');
        }

        // Ensure mail configuration is applied from settings before sending
        $this->applyMailConfigFromSettings();

        try {
            Mail::to($email)->send(new \App\Mail\TestEmail());
            return redirect()->back()->with('success', 'Test email sent successfully to ' . $email);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Test payment gateway connection
     */
    public function testGateway(Request $request, string $gateway)
    {
        // Permission check
        $user = auth()->user();
        if (!$user instanceof User || !$user->hasPermission(AdminRole::PERMISSION_SETTINGS_EDIT)) {
            return redirect()->back()->with('error', 'You do not have permission to test gateways.');
        }

        $validGateways = ['paystack', 'kora', 'stripe'];

        if (!in_array($gateway, $validGateways)) {
            return redirect()->back()->with('error', 'Invalid payment gateway.');
        }

        try {
            $result = $this->testGatewayConnection($gateway);
            return redirect()->back()->with($result['status'], $result['message']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gateway test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test gateway connection
     */
    protected function testGatewayConnection(string $gateway): array
    {
        switch ($gateway) {
            case 'paystack':
                $secretKey = SystemSetting::getDecrypted('paystack_secret_key');
                $sandbox = SystemSetting::getBool('paystack_sandbox');

                if (empty($secretKey)) {
                    return ['status' => 'error', 'message' => 'Paystack secret key not configured.'];
                }

                $url = $sandbox
                    ? 'https://api.paystack.co/transaction/mock'
                    : 'https://api.paystack.co/transaction/initialize';

                // Simple test - just verify key format
                if (strpos($secretKey, 'sk_') !== 0) {
                    return ['status' => 'error', 'message' => 'Invalid Paystack secret key format.'];
                }

                return ['status' => 'success', 'message' => 'Paystack connection successful (sandbox mode: ' . ($sandbox ? 'ON' : 'OFF') . ')'];

            case 'kora':
                $secretKey = SystemSetting::getDecrypted('kora_secret_key');
                $sandbox = SystemSetting::getBool('kora_sandbox');

                if (empty($secretKey)) {
                    return ['status' => 'error', 'message' => 'Kora secret key not configured.'];
                }

                return ['status' => 'success', 'message' => 'Kora connection successful (sandbox mode: ' . ($sandbox ? 'ON' : 'OFF') . ')'];

            case 'stripe':
                $secretKey = SystemSetting::getDecrypted('stripe_secret_key');
                $sandbox = SystemSetting::getBool('stripe_sandbox');

                if (empty($secretKey)) {
                    return ['status' => 'error', 'message' => 'Stripe secret key not configured.'];
                }

                if (strpos($secretKey, 'sk_') !== 0) {
                    return ['status' => 'error', 'message' => 'Invalid Stripe secret key format.'];
                }

                return ['status' => 'success', 'message' => 'Stripe connection successful (sandbox mode: ' . ($sandbox ? 'ON' : 'OFF') . ')'];

            default:
                return ['status' => 'error', 'message' => 'Unknown gateway.'];
        }
    }

    /**
     * Trigger cron job manually
     */
    public function triggerCron(Request $request, string $cronType)
    {
        $validTypes = ['task_expiry', 'referral_bonus', 'daily_streak', 'fraud_scan'];

        if (!in_array($cronType, $validTypes)) {
            return redirect()->back()->with('error', 'Invalid cron type.');
        }

        // Execute the cron job
        try {
            switch ($cronType) {
                case 'task_expiry':
                    Artisan::call('tasks:expire');
                    break;
                case 'referral_bonus':
                    Artisan::call('referrals:distribute');
                    break;
                case 'daily_streak':
                    Artisan::call('streaks:reset');
                    break;
                case 'fraud_scan':
                    Artisan::call('fraud:scan');
                    break;
            }

            return redirect()->back()->with('success', ucfirst(str_replace('_', ' ', $cronType)) . ' cron executed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cron execution failed: ' . $e->getMessage());
        }
    }

    /**
     * View audit logs
     */
    public function auditLogs(Request $request)
    {
        $query = \App\Models\SettingsAuditLog::with('admin');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('setting_key', 'like', "%{$search}%")
                  ->orWhereHas('admin', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.settings.audit', compact('logs'));
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults()
    {
        SystemSetting::initializeDefaults();

        return redirect()->route('admin.settings')->with('success', 'Default settings have been initialized.');
    }

    /**
     * Clear settings cache
     */
    public function clearCache()
    {
        Cache::flush();

        return redirect()->back()->with('success', 'Settings cache cleared successfully.');
    }

    /**
     * Apply mail configuration from system settings into runtime config and rebind mailer
     */
    protected function applyMailConfigFromSettings(): void
    {
        try {
            $enabled = SystemSetting::getBool('smtp_enabled', false);

            $selectedDriver = SystemSetting::get('smtp_driver', config('mail.default'));
            $isTurbo = $selectedDriver === 'turbosmtp';
            $driver = $isTurbo ? 'smtp' : $selectedDriver;

            $host = SystemSetting::get('smtp_host', config('mail.mailers.smtp.host'));
            if (empty($host) && $isTurbo) {
                $host = config('services.turbosmtp.server', $host);
            }

            $port = SystemSetting::getNumber('smtp_port', config('mail.mailers.smtp.port'));
            if ((empty($port) || $port <= 0) && $isTurbo) {
                $port = (int) config('services.turbosmtp.port', 587);
            }

            $username = SystemSetting::get('smtp_username', config('mail.mailers.smtp.username'));
            if (empty($username) && $isTurbo) {
                $username = config('services.turbosmtp.username', $username);
            }

            $password = SystemSetting::getDecrypted('smtp_password', config('mail.mailers.smtp.password'));
            if (empty($password) && $isTurbo) {
                $password = config('services.turbosmtp.password', $password);
            }

            $encryption = strtolower((string) SystemSetting::get('smtp_encryption', config('mail.mailers.smtp.encryption')));
            if (in_array($encryption, ['', 'none', 'null'], true)) {
                $encryption = null;
            }

            $port = (int) $port;
            if ($port <= 0) {
                $port = $encryption === 'ssl' ? 465 : 587;
            }

            if ($encryption === 'ssl' && $port === 587) {
                $port = 465;
            }
            if (($encryption === 'tls' || $encryption === null) && $port === 465) {
                $port = 587;
            }

            $fromAddress = SystemSetting::get('smtp_from_email', config('mail.from.address'));
            if (empty($fromAddress) && $isTurbo) {
                $fromAddress = config('services.turbosmtp.from_address', $fromAddress);
            }

            $fromName = SystemSetting::get('smtp_from_name', config('mail.from.name'));
            if (empty($fromName) && $isTurbo) {
                $fromName = config('services.turbosmtp.from_name', $fromName);
            }

            if ($enabled) {
                Config::set('mail.default', $driver);
                Config::set('mail.mailers.smtp.host', $host);
                Config::set('mail.mailers.smtp.port', $port);
                Config::set('mail.mailers.smtp.username', $username);
                Config::set('mail.mailers.smtp.password', $password);
                Config::set('mail.mailers.smtp.encryption', $encryption);
                Config::set('mail.mailers.smtp.timeout', 30);
                Config::set('mail.mailers.smtp.auth_mode', null);
                Config::set('mail.from.address', $fromAddress);
                Config::set('mail.from.name', $fromName);

                // Force Laravel to rebind mailer instances so changes apply immediately
                app()->forgetInstance('mail.manager');
                app()->forgetInstance('mailer');
            }
        } catch (\Exception $e) {
            // Don't block on mail config application during settings save
        }
    }

    /**
     * Show notification messages management page
     */
    public function notificationMessages()
    {
        $settingsByKey = SystemSetting::getByGroup('notification');
        
        return view('admin.settings.notifications', [
            'settingsByKey' => $settingsByKey,
            'pageTitle' => 'Email Notification Messages'
        ]);
    }

    /**
     * Notification audit trail (in-app notifications)
     */
    public function notificationAudit(Request $request)
    {
        $query = Notification::query()
            ->with(['user:id,name,email,is_admin,admin_role_id'])
            ->latest();

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $type = trim((string) $request->query('type', ''));
        if ($type !== '') {
            $query->where('type', $type);
        }

        $adminOnly = filter_var($request->query('admin_only', false), FILTER_VALIDATE_BOOLEAN);
        if ($adminOnly) {
            $query->whereHas('user', function ($uq) {
                $uq->where('is_admin', true)->orWhereNotNull('admin_role_id');
            });
        }

        $notifications = $query->paginate(100);
        $notifications->appends($request->query());

        $summary = [
            'total' => Notification::count(),
            'today' => Notification::whereDate('created_at', now()->toDateString())->count(),
            'unread' => Notification::where('is_read', false)->count(),
            'admin_total' => Notification::whereHas('user', function ($uq) {
                $uq->where('is_admin', true)->orWhereNotNull('admin_role_id');
            })->count(),
        ];

        $types = Notification::query()
            ->select('type')
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('admin.settings.notifications-audit', compact('notifications', 'summary', 'types', 'search', 'type', 'adminOnly'));
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email'
        ]);

        try {
            // Apply mail config from settings
            $this->applyMailConfigFromSettings();

            Mail::raw('This is a test email from ' . config('app.name'), function ($message) use ($request) {
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($request->test_email)
                    ->subject('Test Email - ' . config('app.name'));
            });

            return redirect()->back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Exception $e) {
            $hint = '';
            $message = $e->getMessage();
            if (str_contains($message, 'Expected response code 250 but got an empty response')) {
                $hint = ' SMTP server closed the connection. Check host, port/encryption pairing (587+tls or 465+ssl), username/password, and ensure outbound SMTP is allowed on your server/firewall.';
            }

            return redirect()->back()->with('error', 'Failed to send test email: ' . $message . $hint);
        }
    }
}
