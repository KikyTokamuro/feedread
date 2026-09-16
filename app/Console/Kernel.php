<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $minutes = (int) config('feedread.refresh_interval_minutes', 30);

        // The cron expression cannot express intervals longer than an hour, so
        // anything out of range simply falls back to hourly.
        if ($minutes < 1 || $minutes > 59) {
            $schedule->command('feeds:refresh')->hourly()->withoutOverlapping();

            return;
        }

        $schedule->command('feeds:refresh')
            ->cron("*/{$minutes} * * * *")
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
