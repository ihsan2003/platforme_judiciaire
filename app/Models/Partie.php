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

    /**
     * Toutes les affectations d'avocats de cette partie (tous dossiers/degrés confondus).
     */
    public function affectationsAvocats()
    {
        return $this->hasMany(PartieAvocat::class, 'id_partie');
    }

    /**
     * Les avocats distincts de cette partie, quel que soit le dossier ou le degré.
     */
    public function avocats()
    {
        return $this->belongsToMany(Avocat::class, 'partie_avocats', 'id_partie', 'id_avocat')
                    ->distinct();
    }

    /**
     * Affectations applicables à un dossier : celles propres à ce dossier
     * + les affectations générales (valables pour tous les dossiers de la partie).
     * Travaille sur la relation chargée (pas de requête supplémentaire si eager loadée).
     */
    public function affectationsPourDossier(int $dossierId)
    {
        return $this->affectationsAvocats
            ->filter(fn (PartieAvocat $a) => $a->id_dossier === null || $a->id_dossier == $dossierId)
            ->sortBy(fn (PartieAvocat $a) => [$a->degre?->ordre ?? 0, $a->id])
            ->values();
    }

    /**
     * Remplace les avocats « généraux » (tous dossiers, tous degrés) de la partie.
     * Les affectations propres à un dossier ou à un degré ne sont pas touchées.
     */
    public function syncAvocatsGeneraux(array $avocatIds): void
    {
        $avocatIds = array_values(array_unique(array_filter($avocatIds)));

        $this->affectationsAvocats()
            ->whereNull('id_dossier')
            ->whereNull('id_degre')
            ->whereNotIn('id_avocat', $avocatIds)
            ->get()
            ->each->delete();

        $deja = $this->affectationsAvocats()
            ->whereNull('id_dossier')
            ->whereNull('id_degre')
            ->pluck('id_avocat')
            ->all();

        foreach (array_diff($avocatIds, $deja) as $idAvocat) {
            $this->affectationsAvocats()->create(['id_avocat' => $idAvocat]);
        }
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