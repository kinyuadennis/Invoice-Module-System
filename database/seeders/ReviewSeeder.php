<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 approved reviews for the landing page
        Review::factory()
            ->count(10)
            ->approved()
            ->withRating(5)
            ->create();

        // Create a few 4-star approved reviews
        Review::factory()
            ->count(3)
            ->approved()
            ->withRating(4)
            ->create();

        // Create some pending reviews for admin moderation
        Review::factory()
            ->count(5)
            ->pending()
            ->create();
    }
}
