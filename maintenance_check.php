<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Build a simulated POST request
$request = Illuminate\Http\Request::create(
    '/admin/settings/registration',
    'POST',
    ['registration_enabled' => 'true'], // Checkbox checked
    [], // cookies
    [], // files
    ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
    null, // content - will be populated from $server params
);

$kernel->bootstrap();

\Illuminate\Support\Facades\Cookie::queue(
    \Illuminate\Cookie\CookieJar::create(
        session_name(),
        '',
        now()->subYear(),
        '/',
        config('session.domain'),
        config('session.secure'),
        true, // httpOnly
        false, // raw
        config('session.same_site')
    )
);

echo "Simulating update() logic for registration_enabled with value=true.\n";
echo "Cookie queue cleared of stale session cookie.\n";
echo "Run: composer dump-autoload && php -S localhost:8000 -t public\n";
echo "Then:  1. Open http://localhost:8000/admin/settings/registration\n";
echo "       2. Verify settings load correctly\n";
echo "       3. Click 'Save Settings'\n";
echo "       4. Check that the green success banner appears (line 21-25 of registration.blade.php)\n";
echo "       5. Refresh page and verify the toggle stays in the selected state\n";
echo "       6. Check DB: SELECT `key`, `value` FROM system_settings WHERE `key`='registration_enabled'; -- value should be 'true' or 'false'\n";
