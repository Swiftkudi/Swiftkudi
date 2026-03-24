<?php

namespace Database\Seeders;

use App\Models\BoostPackage;
use Illuminate\Database\Seeder;

class BoostPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter Boost',
                'slug' => 'starter-boost',
                'description' => 'Perfect for new listings',
                'price' => 500,
                'duration_days' => 3,
                'is_active' => true,
                'icon' => '⭐',
                'color' => '#FDB022',
                'position' => 1,
            ],
            [
                'name' => 'Pro Boost',
                'slug' => 'pro-boost',
                'description' => 'Best for serious sellers',
                'price' => 1500,
                'duration_days' => 7,
                'is_active' => true,
                'icon' => '💎',
                'color' => '#1F2937',
                'position' => 2,
            ],
            [
                'name' => 'Premium Boost',
                'slug' => 'premium-boost',
                'description' => 'Maximum exposure',
                'price' => 3000,
                'duration_days' => 14,
                'is_active' => true,
                'icon' => '👑',
                'color' => '#DC2626',
                'position' => 3,
            ],
            [
                'name' => 'Ultimate Boost',
                'slug' => 'ultimate-boost',
                'description' => 'For maximum results',
                'price' => 5000,
                'duration_days' => 30,
                'is_active' => true,
                'icon' => '🚀',
                'color' => '#6366F1',
                'position' => 4,
            ],
        ];

        foreach ($packages as $package) {
            BoostPackage::create($package);
        }
    }
}
