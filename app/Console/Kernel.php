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
        // Daily lifecycle sweeps. withoutOverlapping keeps a slow mail queue on one
        // day from stacking a second sweep on top of it the next.
        $schedule->command('reminders:warranty-expiry')
            ->dailyAt('07:00')
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('reminders:maintenance-due')
            ->dailyAt('07:15')
            ->withoutOverlapping()
            ->onOneServer();
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