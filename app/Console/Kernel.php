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
        $schedule->command('app:live')->everyMinute();
        $schedule->command('db:backup')->daily();
        $schedule->command('queue:retry all')->everyMinute();
        $schedule->command('queue:flush')->everyMinute();
        // $schedule->command('job:clean-completed-jobs')->daily();
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
