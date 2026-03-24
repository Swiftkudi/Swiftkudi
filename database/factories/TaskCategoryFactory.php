<?php

namespace Database\Factories;

use App\Models\TaskCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TaskCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'platform' => $this->faker->randomElement(['instagram', 'twitter', 'tiktok', 'youtube', 'facebook']),
            'task_type' => $this->faker->randomElement(['micro', 'ugc', 'referral', 'premium']),
            'proof_type' => $this->faker->randomElement(['screenshot', 'video', 'link']),
            'base_price' => $this->faker->numberBetween(100, 5000),
            'min_price' => $this->faker->numberBetween(50, 100),
            'max_price' => $this->faker->numberBetween(500, 10000),
            'platform_margin' => $this->faker->numberBetween(15, 35),
            'min_level' => 1,
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    /**
     * Indicate that the category is active.
     */
    public function active(): self
    {
        return $this->state(['is_active' => true]);
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): self
    {
        return $this->state(['is_active' => false]);
    }

    /**
     * Indicate that the category is featured.
     */
    public function featured(): self
    {
        return $this->state(['is_featured' => true]);
    }

    /**
     * Create a micro task category.
     */
    public function micro(): self
    {
        return $this->state(['task_type' => 'micro']);
    }

    /**
     * Create a UGC task category.
     */
    public function ugc(): self
    {
        return $this->state(['task_type' => 'ugc']);
    }

    /**
     * Create a premium task category.
     */
    public function premium(): self
    {
        return $this->state(['task_type' => 'premium', 'min_level' => 2]);
    }
}
