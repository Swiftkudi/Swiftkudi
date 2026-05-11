<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Http\Controllers\OnboardingController;
use Illuminate\Http\Request;

// Create buyer with no features
$buyer = User::firstOrCreate(
    ['email' => 'singlefeature_test@example.com'],
    [
        'name' => 'Single Feature Test',
        'password' => bcrypt('password'),
        'account_type' => 'buyer',
        'is_admin' => false,
    ]
);
$buyer->buyer_features = null;
$buyer->save();

echo "=== Test 1: Never unlocked feature ===\n";
$request = Request::create('/onboarding/feature/unlock/professional_services', 'GET');
$request->setUser($buyer);
$controller = new OnboardingController();
$response = $controller->showSingleFeatureUnlock($request, 'professional_services');
// The response is a View instance; we can inspect data
$data = $response->getData();
echo "Feature: " . $data['feature'] . "\n";
echo "isUnlocked: " . ($data['isUnlocked'] ? 'true' : 'false') . "\n";
echo "expiresAt: " . ($data['expiresAt'] ?? 'null') . "\n";
echo "showRenewOptions: " . ($data['showRenewOptions'] ? 'true' : 'false') . "\n";
echo "isExpired: " . (isset($data['isExpired']) ? ($data['isExpired'] ? 'true' : 'false') : 'not set') . "\n";
// Check periods
foreach ($data['periods'] as $periodKey => $periodData) {
    $isRenew = in_array($periodKey, ['monthly','quarterly']);
    $shouldShow = $data['showRenewOptions'] ? $isRenew : !$isRenew;
    echo "Period '$periodKey': cost={$periodData['cost']}, months={$periodData['months']}, should show: " . ($shouldShow ? 'YES' : 'NO') . "\n";
}

echo "\n=== Test 2: Active feature ===\n";
$buyer->unlockBuyerFeature('task_creation', 3);
$buyer->refresh();
$request2 = Request::create('/onboarding/feature/unlock/task_creation', 'GET');
$request2->setUser($buyer);
$response2 = $controller->showSingleFeatureUnlock($request2, 'task_creation');
$data2 = $response2->getData();
echo "Feature: " . $data2['feature'] . "\n";
echo "isUnlocked: " . ($data2['isUnlocked'] ? 'true' : 'false') . "\n";
echo "expiresAt: " . ($data2['expiresAt'] ? $data2['expiresAt']->format('Y-m-d') : 'null') . "\n";
echo "showRenewOptions: " . ($data2['showRenewOptions'] ? 'true' : 'false') . "\n";
echo "Expected: showRenewOptions=true, isUnlocked=true\n";

echo "\n=== Test 3: Expired feature ===\n";
// Expire the task_creation
$features = $buyer->buyer_features;
$features['task_creation']['expires_at'] = now()->subDays(5)->toDateTimeString();
$buyer->buyer_features = $features;
$buyer->save();
$buyer->refresh();

$request3 = Request::create('/onboarding/feature/unlock/task_creation', 'GET');
$request3->setUser($buyer);
$response3 = $controller->showSingleFeatureUnlock($request3, 'task_creation');
$data3 = $response3->getData();
echo "Feature: " . $data3['feature'] . "\n";
echo "isUnlocked: " . ($data3['isUnlocked'] ? 'true' : 'false') . "\n";
echo "expiresAt: " . ($data3['expiresAt'] ? $data3['expiresAt']->format('Y-m-d') : 'null') . "\n";
echo "showRenewOptions: " . ($data3['showRenewOptions'] ? 'true' : 'false') . "\n";
echo "isExpired: " . ($data3['isExpired'] ? 'true' : 'false') . "\n";
echo "Expected: showRenewOptions=true (has expiry), isUnlocked=false, isExpired=true\n";

// Cleanup
$buyer->delete();
echo "\nTest buyer cleaned up.\n";
