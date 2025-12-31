<?php

namespace App\Console\Commands;

use App\Models\Estimate;
use Illuminate\Console\Command;

class ExpireEstimates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estimates:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark estimates as expired if they have passed their expiry date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expired estimates...');

        $expiredCount = Estimate::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->toDateString())
            ->where('status', '!=', 'converted')
            ->where('status', '!=', 'expired')
            ->update(['status' => 'expired']);

        $this->info("Marked {$expiredCount} estimate(s) as expired.");

        return Command::SUCCESS;
    }
}
