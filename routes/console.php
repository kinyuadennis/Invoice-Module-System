<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule cleanup of old audit logs (daily, retention: 24 months)
Schedule::job(new \App\Jobs\CleanupOldAuditLogs(24))->daily();
