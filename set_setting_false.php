<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'Before: ' . \App\Models\SystemSetting::get('email_verification_required') . PHP_EOL;
echo 'Boolean before: ' . (\App\Models\SystemSetting::isEmailVerificationRequired() ? 'true' : 'false') . PHP_EOL;

\App\Models\SystemSetting::set('email_verification_required', 'false', 'registration', 'boolean');

echo 'After: ' . \App\Models\SystemSetting::get('email_verification_required') . PHP_EOL;
echo 'Boolean after: ' . (\App\Models\SystemSetting::isEmailVerificationRequired() ? 'true' : 'false') . PHP_EOL;
?>