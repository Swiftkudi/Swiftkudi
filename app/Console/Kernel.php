<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Run revenue aggregation nightly at 02:00
        $schedule->command('revenue:aggregate')->dailyAt('02:00')->withoutOverlapping();

        // Run activation reminder check every hour
        $schedule->command('swiftkudi:send-activation-reminders')->hourly();

        // Auto-approve pending completions older than 48 hours, run every 30 minutes
        $schedule->command('tasks:auto-approve')->everyThirtyMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
