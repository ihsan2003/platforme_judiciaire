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
     * Toutes les affectations de cet avocat (partie × dossier × degré).
     */
    public function affectations()
    {
        return $this->hasMany(PartieAvocat::class, 'id_avocat');
    }

    /**
     * Toutes les parties que cet avocat représente, via partie_avocats
     * (quel que soit le dossier ou le degré).
     */
    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'partie_avocats', 'id_avocat', 'id_partie')
                    ->distinct();
    }

    /**
     * Remplace les parties « générales » de l'avocat (tous dossiers, tous degrés).
     * Les affectations propres à un dossier ou à un degré ne sont pas touchées.
     */
    public function syncPartiesGenerales(array $partieIds): void
    {
        $partieIds = array_values(array_unique(array_filter($partieIds)));

        $this->affectations()
            ->whereNull('id_dossier')
            ->whereNull('id_degre')
            ->whereNotIn('id_partie', $partieIds)
            ->get()
            ->each->delete();

        $deja = $this->affectations()
            ->whereNull('id_dossier')
            ->whereNull('id_degre')
            ->pluck('id_partie')
            ->all();

        foreach (array_diff($partieIds, $deja) as $idPartie) {
            $this->affectations()->create(['id_partie' => $idPartie]);
        }
    }

    /**
     * Tous les dossiers judiciaires dans lesquels cet avocat intervient :
     *  - dossiers où il a une affectation propre, ou
     *  - dossiers des parties pour lesquelles il a une affectation générale.
     */
    public function dossiers()
    {
        $id = $this->id;

        return DossierJudiciaire::where(function ($q) use ($id) {
            $q->whereIn(
                'dossier_judiciaires.id',
                PartieAvocat::where('id_avocat', $id)->whereNotNull('id_dossier')->select('id_dossier')
            )->orWhereHas('parties', function ($p) use ($id) {
                $p->whereIn(
                    'parties.id',
                    PartieAvocat::where('id_avocat', $id)->whereNull('id_dossier')->select('id_partie')
                );
            });
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