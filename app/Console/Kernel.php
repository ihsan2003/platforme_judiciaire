<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CheckDelaisRecours;
use App\Console\Commands\CheckAudiencesProches;
use App\Console\Commands\CheckReclamationsEnAttente;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CheckDelaisRecours::class,
        CheckAudiencesProches::class,
        CheckReclamationsEnAttente::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('recours:check-delais')->daily();
        $schedule->command('notifications:delais-recours')->daily();
        $schedule->command('notifications:audiences-proches')->daily();
        $schedule->command('notifications:reclamations-attente')->daily();
    }
}