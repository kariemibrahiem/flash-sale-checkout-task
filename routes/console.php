<?php

use Illuminate\Support\Facades\Artisan;

// created by kariem ibrahiem
Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

