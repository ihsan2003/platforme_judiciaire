<?php

namespace App\Policies;

use App\Models\Avertissement;
use App\Models\User;

class AvertissementPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view dossiers');
    }

    public function view(User $user, Avertissement $avertissement): bool
    {
        return $user->hasPermissionTo('view dossiers');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Avertissement $avertissement): bool
    {
        return $user->hasPermissionTo('edit dossiers');
    }

    /** Suppression réservée à l'admin (comme pour les dossiers). */
    public function delete(User $user, Avertissement $avertissement): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Avertissement $avertissement): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Avertissement $avertissement): bool
    {
        return $user->hasRole('admin');
    }
}
