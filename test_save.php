<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the app
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\SystemSetting;

echo "=== Current registration_enabled value before test ===\n";
echo "getBool: " . var_export(SystemSetting::getBool('registration_enabled', false), true) . "\n";
echo "get (raw): " . var_export(SystemSetting::get('registration_enabled'), true) . "\n\n";

// Simulate what update() does: checked → value='true'
// $value = $request->has($key) ? 'true' : 'false';
echo "=== Simulating CHECKED submit (value = 'true') ===\n";
SystemSetting::set('registration_enabled', 'true', 'registration', 'boolean');
echo "DB getBool: " . var_export(SystemSetting::getBool('registration_enabled', false), true) . "\n";
echo "DB get raw: " . var_export(SystemSetting::get('registration_enabled'), true) . "\n";
$dbVal = SystemSetting::get('registration_enabled');
$checkedOutput = (bool) ($dbVal ?? true) ? 'checked' : 'unchecked';
echo "Blade checked output: {$checkedOutput}\n\n";

// Simulate what update() does: UNCHECKED → value='false'
echo "=== Simulating UNCHECKED submit (value = 'false') ===\n";
SystemSetting::set('registration_enabled', 'false', 'registration', 'boolean');
echo "DB getBool: " . var_export(SystemSetting::getBool('registration_enabled', false), true) . "\n";
echo "DB get raw: " . var_export(SystemSetting::get('registration_enabled'), true) . "\n";
$dbVal = SystemSetting::get('registration_enabled');
$checkedOutput = (bool) ($dbVal ?? true) ? 'checked' : 'unchecked';
echo "Blade checked output: {$checkedOutput}\n\n";

// Restore default
SystemSetting::set('registration_enabled', 'true', 'registration', 'boolean');
echo "=== Restored to default (true) ===\n";
echo "getBool: " . var_export(SystemSetting::getBool('registration_enabled', false), true) . "\n";
