<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'withdrawable_balance' => $this->faker->randomFloat(2, 0, 10000),
            'promo_credit_balance' => $this->faker->randomFloat(2, 0, 2000),
            'total_earned' => 0,
            'total_spent' => 0,
            'pending_balance' => 0,
            'escrow_balance' => 0,
            'is_activated' => false,
            'activated_at' => null,
        ];
    }
}
