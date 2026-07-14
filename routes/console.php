<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Génération quotidienne des notifications ──────────────────────
// Tous les jours à 7h00 du matin
Schedule::command('notifications:generer --nettoyer')
    ->dailyAt('10:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/notifications.log'));

// ── Optionnel : une deuxième passe en milieu de journée ───────────
// pour les audiences créées après 7h
Schedule::command('notifications:generer')
    ->everyMinute()
    ->withoutOverlapping();
