<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Audience;
use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\Execution;
use App\Models\Finance;
use App\Models\Jugement;
use InvalidArgumentException;

/**
 * Permet de réutiliser la DossierPolicy existante pour les entités qui
 * sont toujours rattachées à un dossier judiciaire (Audience, Jugement,
 * Execution, Finance, DossierTribunal, DossierPartie), même quand la
 * route ne passe pas explicitement par /dossiers/{dossier}/....
 */
trait AuthorizesViaDossier
{
    protected function dossierFrom(object $model): DossierJudiciaire
    {
        return match (true) {
            $model instanceof DossierJudiciaire => $model,
            $model instanceof DossierTribunal   => $model->dossier,
            $model instanceof Audience,
            $model instanceof Jugement          => $model->dossierTribunal->dossier,
            $model instanceof Execution,
            $model instanceof Finance           => $model->jugement->dossierTribunal->dossier,
            default => throw new InvalidArgumentException(
                'Modèle non pris en charge par AuthorizesViaDossier: ' . get_class($model)
            ),
        };
    }

    /**
     * Raccourci : $this->authorizeDossier('update', $audience);
     */
    protected function authorizeDossier(string $ability, object $model): void
    {
        $this->authorize($ability, $this->dossierFrom($model));
    }
}