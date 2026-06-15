<?php

namespace App\Observers;

use App\Models\Jugement;

class JugementObserver
{
    /**
     * Handle the Jugement "created" event.
     */
    public function created(Jugement $jugement): void
    {
        //
    }

    public function saved(Jugement $jugement): void
    {
        $jugement->dossierTribunal->dossier->recalculerStatut();
    }

    /**
     * Handle the Jugement "updated" event.
     */
    public function updated(Jugement $jugement): void
    {
        $jugement->dossierTribunal->dossier->recalculerStatut();
    }

    /**
     * Handle the Jugement "deleted" event.
     */
    public function deleted(Jugement $jugement): void
    {
        $jugement->dossierTribunal->dossier->recalculerStatut();
    }

    /**
     * Handle the Jugement "restored" event.
     */
    public function restored(Jugement $jugement): void
    {
        //
    }

    /**
     * Handle the Jugement "force deleted" event.
     */
    public function forceDeleted(Jugement $jugement): void
    {
        //
    }
}
