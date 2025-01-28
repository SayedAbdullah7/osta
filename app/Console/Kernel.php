<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\MakeServiceClass::class,
        \App\Console\Commands\MakeRepository::class,
        \App\Console\Commands\MakeInterface::class,
        \App\Console\Commands\MakeRepositoryAndService::class,
//        \App\Console\Commands\MakeDataTable::class,
        \App\Console\Commands\GenerateDataTable::class,
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
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
