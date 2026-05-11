<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'Setting value: ' . \App\Models\SystemSetting::get('email_verification_required') . PHP_EOL;
echo 'Boolean result: ' . (\App\Models\SystemSetting::isEmailVerificationRequired() ? 'true' : 'false') . PHP_EOL;
?>