<?php
// app/Console/Commands/GenererNotifications.php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class GenererNotifications extends Command
{
    protected $signature   = 'notifications:generer
                                {--utilisateur= : ID d\'un utilisateur spécifique (optionnel)}
                                {--nettoyer     : Nettoyer les anciennes notifications lues}';

    protected $description = 'Génère les notifications d\'alerte basées sur les délais métier';

    public function handle(NotificationService $service): int
    {
        $this->info('🔔 Génération des notifications...');

        if ($userId = $this->option('utilisateur')) {
            $utilisateur = \App\Models\User::find($userId);

            if (! $utilisateur) {
                $this->error("Utilisateur #{$userId} introuvable.");
                return self::FAILURE;
            }

            $count = $service->genererPourUtilisateur($utilisateur);
            $this->info("✅ {$count} nouvelle(s) notification(s) créée(s) pour {$utilisateur->name}.");
        } else {
            $count = $service->genererToutesLesNotifications();
            $this->info("✅ {$count} nouvelle(s) notification(s) créée(s) au total.");
        }

        if ($this->option('nettoyer')) {
            $supprimees = $service->nettoyerAnciennesNotifications();
            $this->info("🗑️  {$supprimees} ancienne(s) notification(s) supprimée(s).");
        }

        return self::SUCCESS;
    }
}