<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices from active recurring invoice templates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating recurring invoices...');

        $service = app(\App\Http\Services\RecurringInvoiceService::class);
        $generated = $service->generateDueInvoices();

        if ($generated > 0) {
            $this->info("Successfully generated {$generated} invoice(s).");
        } else {
            $this->info('No recurring invoices due for generation.');
        }

        return Command::SUCCESS;
    }
}
