<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlatformFee>
 */
class PlatformFeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'paid', 'waived'];
        $status = $this->faker->randomElement($statuses);

        return [
            'invoice_id' => Invoice::factory(),
            'fee_amount' => 0, // Will be calculated based on invoice total
            'fee_status' => $status,
        ];
    }
}
