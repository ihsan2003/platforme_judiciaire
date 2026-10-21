<?php
// app/Models/Partie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Partie extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'parties';
    
    protected $fillable = [
        'nom_partie',
        'type_personne',
        'identifiant_unique',
        'date_naissance',
        'telephone',
        'email',
        'adresse',
        'est_entraide',
    ];

    protected $casts = [
        'est_entraide' => 'boolean',  
        'date_naissance' => 'date',
    ];

    public function dossiers()
    {
        return $this->belongsToMany(DossierJudiciaire::class, 'dossier_parties', 'id_partie', 'id_dossier')
                    ->withPivot(['id_type_partie', 'id_avocat', 'date_entree'])
                    ->withTimestamps();
    }

    public function jugements()
    {
        return $this->belongsToMany(Jugement::class, 'jugement_parties', 'id_partie', 'id_jugement')
                    ->withPivot(['id_position_institution', 'montant_condamne'])
                    ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'id_partie');
    }

    /** Lignes dossier_parties de cette partie (une par dossier / rôle). */
    public function dossierParties()
    {
        return $this->hasMany(DossierPartie::class, 'id_partie');
    }

    /** Avertissements (إنذارات) où cette partie est المدعي. */
    public function avertissementsDemandeur()
    {
        return $this->hasMany(Avertissement::class, 'id_demandeur');
    }

    /** Avertissements (إنذارات) où cette partie est المدعى عليه. */
    public function avertissementsDefendeur()
    {
        return $this->hasMany(Avertissement::class, 'id_defendeur');
    }

    /**
     * Tous les avertissements de la partie (demandeur ou défendeur),
     * le plus récent d'abord. Chaque élément porte l'attribut `role_partie`
     * ('demandeur' | 'defendeur').
     *
     * Nécessite que les deux relations soient déjà chargées pour éviter
     * des requêtes supplémentaires.
     */
    public function tousAvertissements()
    {
        $demandeur = $this->avertissementsDemandeur
            ->each(fn ($a) => $a->setAttribute('role_partie', 'demandeur'));

        $defendeur = $this->avertissementsDefendeur
            ->each(fn ($a) => $a->setAttribute('role_partie', 'defendeur'));

        return $demandeur->concat($defendeur)
            ->sortByDesc('created_at')
            ->values();
    }

    public function estInstitutionDansDossier($dossierId): bool
    {
        return (bool) $this->est_entraide;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll()
        ->useLogName('parties');

    }
}