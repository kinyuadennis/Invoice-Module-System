<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearDompdfFontCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dompdf:clear-font-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Dompdf font cache to fix font glyph errors';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fontDir = storage_path('fonts');

        if (! File::exists($fontDir)) {
            File::makeDirectory($fontDir, 0755, true);
            $this->info('Created font directory: '.$fontDir);
        }

        $files = File::glob($fontDir.'/*');

        if (empty($files)) {
            $this->info('Font cache is already empty.');

            return Command::SUCCESS;
        }

        $deleted = 0;
        foreach ($files as $file) {
            if (File::isFile($file)) {
                File::delete($file);
                $deleted++;
            }
        }

        $this->info("Cleared {$deleted} font cache file(s).");

        return Command::SUCCESS;
    }
}
