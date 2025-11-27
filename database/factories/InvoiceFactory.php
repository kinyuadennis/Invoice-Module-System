<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['draft', 'sent', 'paid', 'overdue'];
        $status = $this->faker->randomElement($statuses);

        $subtotal = $this->faker->randomFloat(2, 5000, 500000);
        $tax = $subtotal * 0.16; // 16% VAT in Kenya
        $total = $subtotal + $tax;

        $dueDate = $status === 'overdue'
            ? $this->faker->dateTimeBetween('-30 days', '-1 day')
            : ($status === 'paid'
                ? $this->faker->dateTimeBetween('-60 days', '-1 day')
                : $this->faker->dateTimeBetween('now', '+30 days'));

        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'status' => $status,
            'due_date' => $dueDate,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
