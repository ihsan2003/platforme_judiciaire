<?php

namespace App\Policies;

use App\Models\DossierJudiciaire;
use App\Models\User;

class DossierPolicy
{
    /**
     * Bypass complet pour super-admin.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null; // Laisser passer aux méthodes suivantes
    }

    /**
     * Voir la liste des dossiers.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view dossiers');
    }

    /**
     * Voir un dossier précis.
     */
    public function view(User $user, DossierJudiciaire $dossier): bool
    {
        return $user->hasPermissionTo('view dossiers');
    }

    /**
     * Créer un nouveau dossier.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create dossiers');
    }

    /**
     * Modifier un dossier.
     */
    public function update(User $user, DossierJudiciaire $dossier): bool
    {
        // Un dossier clôturé ne peut être modifié que par un responsable
        if ($dossier->statutDossier?->statut_dossier === 'حفظ') {
            return $user->hasRole(['admin']);
        }

        return $user->hasPermissionTo('edit dossiers');
    }

    /**
     * Archiver (soft delete) un dossier.
     * Réservé au compte admin.
     */
    public function delete(User $user, DossierJudiciaire $dossier): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Restaurer un dossier archivé.
     */
    public function restore(User $user, DossierJudiciaire $dossier): bool
    {
        return $user->hasRole(['admin']);
    }

    /**
     * Supprimer définitivement.
     */
    public function forceDelete(User $user, DossierJudiciaire $dossier): bool
    {
        return $user->hasRole('admin');
    }
}