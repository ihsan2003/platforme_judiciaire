<?php
// app/Models/Avocat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Avocat extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'avocats';

    protected $fillable = [
        'nom_avocat',
        'telephone',
        'email',
    ];

    // ─── Relations ────────────────────────────────────────────────────────
    

    /**
     * Toutes les parties que cet avocat représente (lien direct via
     * parties.id_avocat, colonne réellement utilisée par le formulaire
     * d'affectation dans AvocatController).
     */
    public function parties()
    {
        return $this->hasMany(Partie::class, 'id_avocat');
    }

    /**
     * Tous les dossiers judiciaires dans lesquels cet avocat intervient,
     * via les parties qu'il représente (Avocat → Partie → dossier_parties).
     */
    public function dossiers()
    {
        return DossierJudiciaire::whereHas('parties', function ($q) {
            $q->where('parties.id_avocat', $this->id);
        });
    }

    /**
     * Toutes les lignes dossier_parties où cet avocat est assigné
     * (permet de savoir sur quels dossiers/parties il intervient).
     */
    public function dossierParties()
    {
        return $this->hasMany(DossierPartie::class, 'id_avocat');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────
    public function scopeActifs($query)
    {
        return $query->whereHas('parties.dossiers', fn($q) => $q->actifs());
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────
    public function getNombresDossiersActifsAttribute(): int
    {
        return $this->dossiers()->actifs()->distinct()->count();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nom_avocat',
                'telephone',
                'email',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('avocats');
    }
}