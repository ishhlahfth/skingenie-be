<?php

namespace App\Http\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    protected function schedule(Schedule $schedule) {
        $schedule->command('scrap-sociolla:fetch')->dailyAt('00:30')->appendOutputTo(storage_path('logs/host_to_host_sync.log'));
    }

    protected function commands() {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
