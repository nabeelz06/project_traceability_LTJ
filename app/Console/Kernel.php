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
        // Check offline devices setiap 30 menit
        $schedule->call(function () {
            \App\Models\Device::where('is_active', true)
                ->where('last_seen_at', '<', now()->subHours(1))
                ->update(['is_active' => false]);
        })->everyThirtyMinutes();

        // Clean old logs (older than 90 days)
        $schedule->call(function () {
            \App\Models\SystemLog::where('created_at', '<', now()->subDays(90))->delete();
        })->daily();
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