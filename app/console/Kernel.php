<?php

namespace App\Console;

use App\Jobs\ClearExpireHolds;
use App\Models\Hold;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    // created by kariem ibrahiem
    /**
     * The Artisan commands provided by your application.
     *
     * You can add your custom commands here if needed.
     *
     * @var array<int, class-string>
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        $schedule->job(new \App\Jobs\ClearExpireHolds())->everyMinute();
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // $this->load(__DIR__.'/Commands');

        // require base_path('routes/console.php');
    }
}
