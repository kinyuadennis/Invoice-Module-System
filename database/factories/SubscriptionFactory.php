<?php

namespace Database\Factories;

use App\Config\PaymentConstants;
use App\Config\SubscriptionConstants;
use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $endsAt = $this->faker->dateTimeBetween('now', '+1 month');
        $nextBillingAt = $this->faker->dateTimeBetween('now', '+1 month');

        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'subscription_plan_id' => SubscriptionPlan::factory(),
            'plan_code' => $this->faker->randomElement(['basic', 'pro', 'enterprise']),
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_PENDING,
            'gateway' => $this->faker->randomElement([PaymentConstants::GATEWAY_MPESA, PaymentConstants::GATEWAY_STRIPE]),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'next_billing_at' => $nextBillingAt,
            'auto_renew' => true,
        ];
    }

    /**
     * Indicate that the subscription is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the subscription is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]);
    }

    /**
     * Indicate that the subscription is in grace period.
     */
    public function grace(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_GRACE,
            'ends_at' => now()->addDays(SubscriptionConstants::RENEWAL_GRACE_DAYS),
        ]);
    }

    /**
     * Indicate that the subscription is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_EXPIRED,
            'ends_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the subscription is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Indicate that the subscription uses M-Pesa gateway.
     */
    public function mpesa(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => PaymentConstants::GATEWAY_MPESA,
        ]);
    }

    /**
     * Indicate that the subscription uses Stripe gateway.
     */
    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => PaymentConstants::GATEWAY_STRIPE,
        ]);
    }
}
