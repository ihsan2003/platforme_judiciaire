<?php

namespace App\Observers;

use App\Models\Execution;

class ExecutionObserver
{
    /**
     * Handle the Execution "created" event.
     */
    public function created(Execution $execution): void
    {
        //
    }

    public function saved(Execution $execution): void
    {
        $execution->jugement->dossierTribunal->dossier->recalculerStatut();
    }

    /**
     * Handle the Execution "updated" event.
     */
    public function updated(Execution $execution): void
    {
        $execution->jugement->dossierTribunal->dossier->recalculerStatut();

    }

    /**
     * Handle the Execution "deleted" event.
     */
    public function deleted(Execution $execution): void
    {
        $execution->jugement->dossierTribunal->dossier->recalculerStatut();

    }

    /**
     * Handle the Execution "restored" event.
     */
    public function restored(Execution $execution): void
    {
        //
    }

    /**
     * Handle the Execution "force deleted" event.
     */
    public function forceDeleted(Execution $execution): void
    {
        //
    }
}
