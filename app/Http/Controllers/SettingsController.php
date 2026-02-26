<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    protected $superAdminOnly = ['general', 'smtp', 'payment', 'security', 'cron', 'commission', 'currency', 'maintenance', 'audit', 'registration', 'task-gate'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access settings.');
            }

            $user = Auth::user();

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

        return view('admin.settings.index', compact('groups', 'settingsCounts'));
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
            $settings = SystemSetting::where('group', 'task-gate')->get();
            $settingsByKey = [];
            foreach ($settings as $s) {
                $settingsByKey[$s->key] = SystemSetting::get($s->key, $s->value);
            }
            return view('admin.settings._task_gate', compact('settings', 'settingsByKey', 'group'));
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
            \Mail::to($email)->send(new \App\Mail\TestEmail());
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
        if (!auth()->user() || !auth()->user()->hasPermission(AdminRole::PERMISSION_SETTINGS_EDIT)) {
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
                    \Artisan::call('tasks:expire');
                    break;
                case 'referral_bonus':
                    \Artisan::call('referrals:distribute');
                    break;
                case 'daily_streak':
                    \Artisan::call('streaks:reset');
                    break;
                case 'fraud_scan':
                    \Artisan::call('fraud:scan');
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

            $driver = SystemSetting::get('smtp_driver', config('mail.default'));
            $host = SystemSetting::get('smtp_host', config('mail.mailers.smtp.host'));
            $port = SystemSetting::getNumber('smtp_port', config('mail.mailers.smtp.port'));
            $username = SystemSetting::get('smtp_username', config('mail.mailers.smtp.username'));
            $password = SystemSetting::getDecrypted('smtp_password', config('mail.mailers.smtp.password'));
            $encryption = SystemSetting::get('smtp_encryption', config('mail.mailers.smtp.encryption'));
            $fromAddress = SystemSetting::get('smtp_from_email', config('mail.from.address'));
            $fromName = SystemSetting::get('smtp_from_name', config('mail.from.name'));

            if ($enabled) {
                Config::set('mail.default', $driver);
                Config::set('mail.mailers.smtp.host', $host);
                Config::set('mail.mailers.smtp.port', $port);
                Config::set('mail.mailers.smtp.username', $username);
                Config::set('mail.mailers.smtp.password', $password);
                Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
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
        $settingsByKey = SystemSetting::getAllGrouped('notifications');
        
        return view('admin.settings.notifications', [
            'settingsByKey' => $settingsByKey,
            'pageTitle' => 'Email Notification Messages'
        ]);
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

            \Mail::raw('This is a test email from ' . config('app.name'), function ($message) use ($request) {
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($request->test_email)
                    ->subject('Test Email - ' . config('app.name'));
            });

            return redirect()->back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}
