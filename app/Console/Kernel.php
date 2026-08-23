<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * アプリケーションのコマンドスケジュールを定義する。
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('reading-plans:send-reminders')
            ->dailyAt('20:00');
    }

    /**
     * アプリケーションのコマンドを登録する。
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
