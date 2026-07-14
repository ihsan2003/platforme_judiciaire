<?php
// app/Console/Kernel.php
// Ajoutez ce qui suit dans la méthode schedule() de votre Kernel existant

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
        // ── Génération quotidienne des notifications ──────────────────────
        // Tous les jours à 7h00 du matin
        $schedule->command('notifications:generer --nettoyer')
                 ->dailyAt('07:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/notifications.log'));

        // ── Optionnel : une deuxième passe en milieu de journée ───────────
        // pour les audiences créées après 7h
        $schedule->command('notifications:generer')
                 ->dailyAt('13:47')
                 ->withoutOverlapping();
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