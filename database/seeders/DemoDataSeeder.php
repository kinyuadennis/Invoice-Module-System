<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PlatformFee;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo user
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@invoicehub.co.ke'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // Create 8 Kenyan business clients
        $kenyanClients = [
            ['name' => 'Safaricom Limited', 'email' => 'info@safaricom.co.ke', 'phone' => '+254722000000', 'address' => 'Safaricom House, Waiyaki Way, Westlands, Nairobi'],
            ['name' => 'Equity Bank Kenya', 'email' => 'contact@equitybank.co.ke', 'phone' => '+254733000000', 'address' => 'Equity Centre, Hospital Road, Upper Hill, Nairobi'],
            ['name' => 'KCB Group', 'email' => 'info@kcbgroup.com', 'phone' => '+254711000000', 'address' => 'Kencom House, Moi Avenue, Nairobi'],
            ['name' => 'East African Breweries', 'email' => 'info@eabl.com', 'phone' => '+254720000000', 'address' => 'Ruaraka Road, Nairobi'],
            ['name' => 'Bamburi Cement', 'email' => 'contact@bamburicement.com', 'phone' => '+254734000000', 'address' => 'Industrial Area, Nairobi'],
            ['name' => 'Kenya Airways', 'email' => 'info@kenya-airways.com', 'phone' => '+254732000000', 'address' => 'JKIA, Embakasi, Nairobi'],
            ['name' => 'Nakumatt Holdings', 'email' => 'info@nakumatt.co.ke', 'phone' => '+254736000000', 'address' => 'Nakumatt House, Mombasa Road, Nairobi'],
            ['name' => 'Uchumi Supermarkets', 'email' => 'contact@uchumi.co.ke', 'phone' => '+254737000000', 'address' => 'Uchumi House, Langata Road, Nairobi'],
        ];

        $clients = [];
        foreach ($kenyanClients as $clientData) {
            $clients[] = Client::firstOrCreate(
                ['email' => $clientData['email']],
                array_merge($clientData, ['user_id' => $demoUser->id])
            );
        }

        // Create 12 invoices with varying statuses
        $statuses = ['draft', 'sent', 'paid', 'overdue'];
        $invoices = [];

        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 12; $i++) {
            $client = $faker->randomElement($clients);
            $status = $faker->randomElement($statuses);

            // Calculate dates based on status
            $dueDate = match ($status) {
                'overdue' => now()->subDays($faker->numberBetween(1, 30)),
                'paid' => now()->subDays($faker->numberBetween(1, 60)),
                default => now()->addDays($faker->numberBetween(1, 30)),
            };

            $subtotal = $faker->randomFloat(2, 5000, 500000);
            $tax = $subtotal * 0.16; // 16% VAT
            $total = $subtotal + $tax;

            $invoice = Invoice::create([
                'client_id' => $client->id,
                'user_id' => $demoUser->id,
                'status' => $status,
                'due_date' => $dueDate,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            $invoices[] = $invoice;

            // Create 2-5 line items for each invoice
            $itemCount = $faker->numberBetween(2, 5);
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

            $invoiceSubtotal = 0;
            for ($j = 0; $j < $itemCount; $j++) {
                $quantity = $faker->numberBetween(1, 10);
                $unitPrice = $faker->randomFloat(2, 500, 50000);
                $totalPrice = $quantity * $unitPrice;
                $invoiceSubtotal += $totalPrice;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $faker->randomElement($services),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            // Recalculate invoice totals based on actual items
            $invoiceTax = $invoiceSubtotal * 0.16;
            $invoiceTotal = $invoiceSubtotal + $invoiceTax;

            $invoice->update([
                'subtotal' => $invoiceSubtotal,
                'tax' => $invoiceTax,
                'total' => $invoiceTotal,
            ]);

            // Create platform fee (5% of total)
            $platformFeeAmount = $invoiceTotal * 0.05;
            $feeStatus = $status === 'paid' ? 'paid' : ($faker->randomElement(['pending', 'paid']));

            PlatformFee::create([
                'invoice_id' => $invoice->id,
                'fee_amount' => $platformFeeAmount,
                'fee_status' => $feeStatus,
            ]);
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('- 8 Kenyan business clients created');
        $this->command->info('- 12 invoices with varying statuses created');
        $this->command->info('- Invoice items and platform fees generated');
    }
}
