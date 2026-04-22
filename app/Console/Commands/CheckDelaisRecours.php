<?php
// app/Console/Commands/CheckDelaisRecours.php

namespace App\Console\Commands;

use App\Models\Jugement;
use Illuminate\Console\Command;

/**
 * Job quotidien qui parcourt tous les jugements non définitifs
 * et les marque automatiquement comme définitifs si le délai
 * légal de recours est expiré sans qu'un recours ait été déposé.
 *
 * Règle métier : Si aucun recours → jugement devient définitif
 *
 * Enregistrement dans app/Console/Kernel.php :
 *   $schedule->command('recours:check-delais')->daily();
 */
class CheckDelaisRecours extends Command
{
    protected $signature   = 'recours:check-delais';
    protected $description = 'Marque les jugements comme définitifs si le délai de recours est expiré';

    public function handle(): int
    {
        $jugements = Jugement::where('est_definitif', false)
            ->whereDoesntHave('recours')
            ->with(['dossierTribunal.dossier'])
            ->get();

        $count = 0;

        foreach ($jugements as $jugement) {
            if ($jugement->verifierEtMarquerDefinitif()) {
                $count++;
                $this->line(
                    "  ✓ Jugement #{$jugement->id} du {$jugement->date_jugement->format('d/m/Y')} → Définitif"
                );
            }
        }

        $this->info("{$count} jugement(s) marqué(s) comme définitif(s).");

        return self::SUCCESS;
    }
}