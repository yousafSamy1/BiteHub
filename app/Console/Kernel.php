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
        // Generate and send daily summary report to admins at 22:00 (10 PM)
        $schedule->command('report:daily-summary')->dailyAt('00:00');
        
        // Dispatch daily orders for active meal plans
        $schedule->command('subscriptions:dispatch-orders')->dailyAt('00:01');

        // Cleanup old notifications
        $schedule->command('notifications:cleanup')->daily();
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
