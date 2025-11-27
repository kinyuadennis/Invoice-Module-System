<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = [
            'Web Development Services',
            'Mobile App Development',
            'Digital Marketing Campaign',
            'Cloud Infrastructure Setup',
            'SEO Optimization',
            'Content Writing Services',
            'Graphic Design Services',
            'Consulting Services',
            'Software Maintenance',
            'Data Analytics Services',
        ];

        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 500, 50000);
        $totalPrice = $quantity * $unitPrice;

        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->randomElement($services),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
        ];
    }
}
