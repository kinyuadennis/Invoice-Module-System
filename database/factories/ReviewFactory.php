<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'John Mwangi', 'Mary Wanjiku', 'David Ochieng', 'Sarah Muthoni',
            'Peter Kamau', 'Grace Njeri', 'James Otieno', 'Lucy Wambui',
            'Michael Kipchoge', 'Esther Chebet', 'Daniel Mwangi', 'Ruth Nyambura',
        ];

        $titles = [
            'Great invoicing platform!', 'Saves me so much time', 'Highly recommend',
            'Best investment for my business', 'Professional and reliable',
            'M-Pesa integration is amazing', 'KRA compliance made easy',
            'Payment tracking is excellent', 'User-friendly interface',
            'Excellent customer support', 'Game changer for cash flow',
        ];

        $contents = [
            'Payment delays cut from 45 to 8 days. Game changer for cash flow.',
            'M-Pesa integration makes getting paid so easy. Clients love it.',
            'Professional invoices that clients actually pay on time. KRA compliance is a bonus.',
            'Best investment for my business. ROI in first month.',
            'The automated reminders have reduced my overdue invoices significantly.',
            'Creating invoices has never been easier. The templates are professional.',
            'The dashboard gives me great insights into my business finances.',
            'Support team is responsive and helpful. Great experience overall.',
            'Multi-currency support is perfect for my international clients.',
            'The platform fee is reasonable for all the features provided.',
        ];

        return [
            'user_id' => null,
            'company_id' => null,
            'name' => fake()->randomElement($names),
            'title' => fake()->randomElement($titles),
            'content' => fake()->randomElement($contents),
            'rating' => fake()->numberBetween(4, 5), // Mostly positive reviews
            'approved' => fake()->boolean(80), // 80% approved by default
        ];
    }

    /**
     * Indicate that the review is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved' => true,
        ]);
    }

    /**
     * Indicate that the review is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved' => false,
        ]);
    }

    /**
     * Set a specific rating.
     */
    public function withRating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => max(1, min(5, $rating)), // Ensure rating is between 1-5
        ]);
    }
}
