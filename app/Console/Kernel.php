<?php

namespace App\Console;

use App\Http\Controllers\API\AuthAPIController;
use App\Models\FrameBin;
use App\Models\FrameContent;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\FrameContentsAPIController;

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
        // $schedule->command('inspire')->hourly();
        /**
         * Delete permanently contents from BIN after 30 days
         */
        $schedule->call(function () {
           $new = new FrameContentsAPIController();
           $new->empty_bin();
        })->everySixHours();
        $schedule->call(function () {
           $verification = new AuthAPIController();
           $verification->empty_verification();
        })->everyTwoMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
