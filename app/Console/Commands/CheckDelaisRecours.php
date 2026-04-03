<?php
// app/Console/Commands/CheckDelaisRecours.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Jugement;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class CheckDelaisRecours extends Command
{
    protected $signature = 'notifications:delais-recours';
    protected $description = 'Vérifie les délais de recours et génère des notifications';

    public function handle()
    {
        $jugements = Jugement::where('est_definitif', false)
            ->whereHas('recours', null)
            ->with('dossierTribunal.dossier')
            ->get();

        foreach ($jugements as $jugement) {
            $typeRecours = $jugement->dossierTribunal->dossier->recours()
                ->first()?->typeRecours;

            if ($typeRecours) {
                $dateLimite = Carbon::parse($jugement->date_jugement)
                    ->addDays($typeRecours->delai_legal_jours);
                
                $joursRestants = Carbon::now()->diffInDays($dateLimite, false);

                if ($joursRestants <= 5 && $joursRestants > 0) {
                    $admins = User::role('ADMIN')->get();

                    foreach ($admins as $admin) {
                        Notification::create([
                            'id_utilisateur' => $admin->id,
                            'type_notification' => 'DELAI_RECOURS',
                            'message' => "Délai de recours expire dans {$joursRestants} jours pour le jugement du dossier {$jugement->dossierTribunal->dossier->numero_dossier_interne}",
                            'id_dossier' => $jugement->dossierTribunal->dossier->id,
                            'date_notification' => now()
                        ]);
                    }
                }
            }
        }

        $this->info('Notifications de délais de recours générées avec succès.');
    }
}