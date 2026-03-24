<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Wallet;
use App\Services\SwiftKudiService;

class ActivationReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_activation_deducts_fee_and_records_revenue()
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'withdrawable_balance' => 1000, 'promo_credit_balance' => 0]);

        $service = $this->app->make(SwiftKudiService::class);
        $res = $service->activateUser($user, null);

        $this->assertTrue($res['success']);
        $this->assertDatabaseHas('wallets', ['user_id' => $user->id, 'is_activated' => 1]);
    }

    public function test_referred_activation_pays_referrer_and_platform()
    {
        $referrer = User::factory()->create();
        $referrerWallet = Wallet::create(['user_id' => $referrer->id, 'withdrawable_balance' => 0, 'promo_credit_balance' => 0]);

        $referred = User::factory()->create();
        $referredWallet = Wallet::create(['user_id' => $referred->id, 'withdrawable_balance' => 2000, 'promo_credit_balance' => 0]);

        $service = $this->app->make(SwiftKudiService::class);
        $res = $service->activateUser($referred, $referrer);

        $this->assertTrue($res['success']);
        $this->assertDatabaseHas('wallets', ['user_id' => $referrer->id, 'withdrawable_balance' => 1000]);
    }
}
