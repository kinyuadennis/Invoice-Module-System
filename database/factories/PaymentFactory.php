<?php

namespace Database\Factories;

use App\Config\PaymentConstants;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'currency' => 'KES',
            'gateway' => $this->faker->randomElement([PaymentConstants::GATEWAY_MPESA, PaymentConstants::GATEWAY_STRIPE]),
            'status' => PaymentConstants::PAYMENT_STATUS_INITIATED,
            'gateway_transaction_id' => $this->faker->uuid(),
            'idempotency_key' => (string) $this->faker->uuid(),
            'payment_date' => now(),
        ];
    }

    /**
     * Indicate that the payment is initiated.
     */
    public function initiated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentConstants::PAYMENT_STATUS_INITIATED,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is successful.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentConstants::PAYMENT_STATUS_SUCCESS,
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentConstants::PAYMENT_STATUS_FAILED,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment timed out.
     */
    public function timeout(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentConstants::PAYMENT_STATUS_TIMEOUT,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is for a subscription.
     */
    public function forSubscription(Subscription $subscription): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $subscription->company_id,
            'payable_type' => Subscription::class,
            'payable_id' => $subscription->id,
            'gateway' => $subscription->gateway,
            'amount' => $subscription->plan->price ?? 1000,
        ]);
    }

    /**
     * Indicate that the payment is for an invoice.
     */
    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $invoice->company_id,
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => $invoice->grand_total,
        ]);
    }

    /**
     * Indicate that the payment uses M-Pesa gateway.
     */
    public function mpesa(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => PaymentConstants::GATEWAY_MPESA,
        ]);
    }

    /**
     * Indicate that the payment uses Stripe gateway.
     */
    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'gateway' => PaymentConstants::GATEWAY_STRIPE,
        ]);
    }
}
