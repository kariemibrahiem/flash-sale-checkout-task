<?php

use Illuminate\Support\Facades\Artisan;
use App\Jobs\ClearExpireHolds;
use Illuminate\Support\Facades\Schedule;

// created by kariem ibrahiem
Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ClearExpireHolds)->everyMinute();
