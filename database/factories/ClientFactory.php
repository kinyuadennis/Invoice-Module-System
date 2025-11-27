<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kenyanBusinesses = [
            ['name' => 'Safaricom Limited', 'email' => 'info@safaricom.co.ke'],
            ['name' => 'Equity Bank Kenya', 'email' => 'contact@equitybank.co.ke'],
            ['name' => 'KCB Group', 'email' => 'info@kcbgroup.com'],
            ['name' => 'East African Breweries', 'email' => 'info@eabl.com'],
            ['name' => 'Bamburi Cement', 'email' => 'contact@bamburicement.com'],
            ['name' => 'Kenya Airways', 'email' => 'info@kenya-airways.com'],
            ['name' => 'Nakumatt Holdings', 'email' => 'info@nakumatt.co.ke'],
            ['name' => 'Uchumi Supermarkets', 'email' => 'contact@uchumi.co.ke'],
        ];

        $business = $this->faker->randomElement($kenyanBusinesses);

        return [
            'name' => $business['name'],
            'email' => $business['email'],
            'phone' => '+254'.$this->faker->numerify('7#########'),
            'address' => $this->faker->streetAddress().', '.$this->faker->city().', Kenya',
        ];
    }
}
