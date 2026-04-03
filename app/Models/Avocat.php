<?php
// app/Models/Avocat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avocat extends Model
{
    use HasFactory;

    protected $table = 'avocats';

    protected $fillable = [
        'nom_avocat',
        'telephone',
        'email',
    ];

    // ─── Relations ────────────────────────────────────────────────────────
    public function dossierParties()
    {
        return $this->hasMany(DossierPartie::class, 'id_avocat');
    }

    /**
     * Tous les dossiers judiciaires dans lesquels cet avocat intervient.
     */
    public function dossiers()
    {
        return $this->hasManyThrough(
            DossierJudiciaire::class,
            DossierPartie::class,
            'id_avocat',   // FK sur dossier_parties
            'id',          // PK sur dossier_judiciaires
            'id',          // PK locale
            'id_dossier'   // FK sur dossier_parties
        );
    }

    /**
     * Toutes les parties que cet avocat représente.
     */
    public function parties()
    {
        return $this->hasManyThrough(
            Partie::class,
            DossierPartie::class,
            'id_avocat',
            'id',
            'id',
            'id_partie'
        );
    }

    // ─── Scopes ───────────────────────────────────────────────────────────
    public function scopeActifs($query)
    {
        return $query->whereHas('dossierParties.dossier', fn($q) => $q->actifs());
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────
    public function getNombresDossiersActifsAttribute(): int
    {
        return $this->dossierParties()
            ->whereHas('dossier', fn($q) => $q->actifs())
            ->distinct('id_dossier')
            ->count('id_dossier');
    }
}
