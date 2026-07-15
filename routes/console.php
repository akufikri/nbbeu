<?php

use App\Jobs\CheckStuckToyyibpayPayments;
use App\Jobs\SendRenewalReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CheckStuckToyyibpayPayments)->everyFifteenMinutes();
Schedule::job(new SendRenewalReminders)->daily();
