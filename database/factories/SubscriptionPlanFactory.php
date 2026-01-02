<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 1000, 50000),
            'currency' => 'KES',
            'billing_period' => $this->faker->randomElement(['monthly', 'yearly', 'quarterly']),
            'max_companies' => $this->faker->numberBetween(1, 10),
            'max_users_per_company' => $this->faker->numberBetween(1, 50),
            'max_invoices_per_month' => $this->faker->numberBetween(100, 10000),
            'max_clients' => $this->faker->numberBetween(50, 1000),
            'features' => [],
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
