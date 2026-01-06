<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if plans already exist to avoid duplicates
        if (SubscriptionPlan::where('slug', 'free')->exists()) {
            $this->command->info('Subscription plans already exist. Skipping seeding.');

            return;
        }

        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started with basic invoicing needs.',
                'price' => 0,
                'currency' => 'KES',
                'billing_period' => 'monthly',
                'max_companies' => 1,
                'max_users_per_company' => 1,
                'max_invoices_per_month' => 3,
                'max_clients' => 5,
                'features' => [
                    '3 invoices/month',
                    'Basic templates',
                    'Email support',
                    'PDF export',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Ideal for small businesses ready to scale their invoicing.',
                'price' => 999,
                'currency' => 'KES',
                'billing_period' => 'monthly',
                'max_companies' => 1,
                'max_users_per_company' => 3,
                'max_invoices_per_month' => -1, // unlimited
                'max_clients' => -1, // unlimited
                'features' => [
                    'Unlimited invoices',
                    'M-Pesa integration',
                    'Auto reminders',
                    'Client portal',
                    'Priority support',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Advanced features for growing businesses and agencies.',
                'price' => 2999,
                'currency' => 'KES',
                'billing_period' => 'monthly',
                'max_companies' => 3,
                'max_users_per_company' => 10,
                'max_invoices_per_month' => -1, // unlimited
                'max_clients' => -1, // unlimited
                'features' => [
                    'Everything in Starter',
                    'KRA eTIMS export',
                    'Recurring billing',
                    'Advanced analytics',
                    'API access',
                    'Dedicated support',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::create($planData);
        }

        $this->command->info('Successfully seeded '.count($plans).' subscription plans.');
    }
}
