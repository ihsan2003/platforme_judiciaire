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
    //
    // RG : un avocat n'est concerné QUE par les dossiers (et degrés) où il a été
    // explicitement affecté (table dossier_partie_avocats). Être lié à une partie
    // ne le rend PAS concerné par tous les dossiers de cette partie.

    /** Affectations de l'avocat : (partie dans un dossier) × degré éventuel. */
    public function affectations()
    {
        return $this->hasMany(DossierPartieAvocat::class, 'id_avocat');
    }

    /** Lignes dossier_parties où l'avocat est affecté. */
    public function dossierParties()
    {
        return $this->hasManyThrough(
            DossierPartie::class,
            DossierPartieAvocat::class,
            'id_avocat',          // FK sur dossier_partie_avocats
            'id',                 // clé de dossier_parties
            'id',                 // clé locale (avocats.id)
            'id_dossier_partie'   // FK vers dossier_parties
        );
    }

    /** Parties que l'avocat représente dans au moins un dossier (requête). */
    public function parties()
    {
        return Partie::whereIn('parties.id', function ($sub) {
            $sub->select('dp.id_partie')
                ->from('dossier_parties as dp')
                ->join('dossier_partie_avocats as dpa', 'dpa.id_dossier_partie', '=', 'dp.id')
                ->where('dpa.id_avocat', $this->id);
        });
    }

    /** Dossiers dans lesquels l'avocat est explicitement affecté (requête). */
    public function dossiers()
    {
        return DossierJudiciaire::whereIn('dossier_judiciaires.id', function ($sub) {
            $sub->select('dp.id_dossier')
                ->from('dossier_parties as dp')
                ->join('dossier_partie_avocats as dpa', 'dpa.id_dossier_partie', '=', 'dp.id')
                ->where('dpa.id_avocat', $this->id);
        });
    }

    // ─── Scopes ───────────────────────────────────────────────────────────
    public function scopeActifs($query)
    {
        return $query->whereHas('affectations.dossierPartie.dossier', fn($q) => $q->actifs());
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