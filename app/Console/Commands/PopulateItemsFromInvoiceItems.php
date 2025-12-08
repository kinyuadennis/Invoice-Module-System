<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateItemsFromInvoiceItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:populate-from-invoice-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate items table from existing invoice_items';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Populating items table from invoice_items...');

        // Get unique item descriptions grouped by company
        $invoiceItems = DB::table('invoice_items')
            ->select('company_id', 'description', DB::raw('AVG(unit_price) as avg_price'))
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->groupBy('company_id', 'description')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($invoiceItems as $invoiceItem) {
            // Check if item already exists
            $exists = Item::where('company_id', $invoiceItem->company_id)
                ->where('name', $invoiceItem->description)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            // Create new item
            Item::create([
                'company_id' => $invoiceItem->company_id,
                'name' => $invoiceItem->description,
                'unit_price' => round((float) $invoiceItem->avg_price, 2),
            ]);

            $created++;
        }

        $this->info("Created {$created} items. Skipped {$skipped} duplicates.");

        return Command::SUCCESS;
    }
}
