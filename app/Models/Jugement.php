<?php
// app/Models/Jugement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Jugement extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'jugements';
    
    protected $fillable = [
        'id_dossier_tribunal',
        'id_juge',
        'date_jugement',
        'contenu_dispositif',
        'est_definitif',
        'created_by'
    ];

    protected $casts = [
        'date_jugement' => 'date',
        'est_definitif' => 'boolean'
    ];

    // ─────────────────────────────────────────
    // RELATIONS
    // ─────────────────────────────────────────

    public function dossierTribunal()
    {
        return $this->belongsTo(DossierTribunal::class, 'id_dossier_tribunal');
    }

    public function juge()
    {
        return $this->belongsTo(Juge::class, 'id_juge');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'jugement_parties', 'id_jugement', 'id_partie')
                    ->withPivot(['id_position_institution', 'montant_condamne'])
                    ->withTimestamps();
    }

    // ─────────────────────────────────────────
    // EXÉCUTION — partie(s) concernée(s)
    // ─────────────────────────────────────────

    /**
     * Libellé de la position de l'institution (مع / ضد) pour ce jugement,
     * déduit de la ligne pivot jugement_parties de la partie est_entraide.
     */
    public function positionInstitutionLabel(): ?string
    {
        $ligneInstitution = $this->parties->first(fn($p) => $p->est_entraide);

        if (! $ligneInstitution || ! $ligneInstitution->pivot->id_position_institution) {
            return null;
        }

        return PositionInstitution::find($ligneInstitution->pivot->id_position_institution)
            ?->position;
    }

    /**
     * true si l'institution est condamnée ("ضد") dans ce jugement.
     */
    public function estContreInstitution(): bool
    {
        return str_contains($this->positionInstitutionLabel() ?? '', 'ضد');
    }

    /**
     * RG — Détermine la ou les parties concernées par l'exécution de ce
     * jugement :
     *  - si l'institution est condamnée ("ضد"), c'est elle qui est
     *    concernée par l'exécution ;
     *  - si l'institution est gagnante ("مع"), ce sont les autres parties
     *    — cochées lors de la création du jugement — qui sont concernées.
     *
     * @return \Illuminate\Support\Collection<int> IDs des parties concernées
     */
    public function partiesIdsConcerneesParExecution()
    {
        if ($this->estContreInstitution()) {
            return $this->parties
                ->filter(fn($p) => $p->est_entraide)
                ->pluck('id');
        }

        return $this->parties
            ->filter(fn($p) => ! $p->est_entraide)
            ->pluck('id');
    }

    public function finance()
    {
        return $this->hasOne(Finance::class, 'id_jugement');
    }

    public function recours()
    {
        return $this->hasMany(Recours::class, 'id_jugement');
    }

    public function executions()
    {
        return $this->hasMany(Execution::class, 'id_jugement');
    }

    // ─────────────────────────────────────────
    // RÈGLES MÉTIER
    // ─────────────────────────────────────────

    /**
     * RG — Un jugement devient définitif lorsqu'aucun recours
     * n'a été déposé dans le délai légal applicable.
     *
     * Appelé par un job planifié (ex: CheckDelaisRecours)
     * ou manuellement via RecoursController::cloturerSansRecours().
     */
    public function verifierEtMarquerDefinitif(): bool
    {
        // Déjà définitif ou recours déjà déposé : rien à faire
        if ($this->est_definitif || $this->recours()->exists()) {
            return false;
        }

        // On prend le délai le plus court parmi tous les types de recours actifs
        $delaiMinimal = TypeRecours::orderBy('delai_legal_jours')->value('delai_legal_jours');

        if (!$delaiMinimal) {
            return false;
        }

        $dateLimite = $this->date_jugement->copy()->addDays($delaiMinimal);

        if (now()->gt($dateLimite)) {
            $this->update(['est_definitif' => true]);

            // Propager la clôture au dossier
            $dossier = $this->dossierTribunal->dossier;
            $statut  = StatutDossier::whereRaw("LOWER(statut_dossier) LIKE '%clôturé%'")->first();

            if ($statut && !$dossier->recours()->exists()) {
                $dossier->update(['id_statut_dossier' => $statut->id]);
            }

            return true;
        }

        return false;
    }

    /**
     * Indique si le jugement peut encore faire l'objet d'un recours.
     * Utilisé dans les vues pour afficher / masquer le bouton "Déposer un recours".
     */
    public function peutFaireObjetRecours(): bool
    {
        if ($this->est_definitif) {
            return false;
        }

        // Recours déjà déposé sur ce jugement
        if ($this->recours()->exists()) {
            return false;
        }

        // Vérifier que le délai légal n'est pas dépassé
        $delaiMinimal = TypeRecours::orderBy('delai_legal_jours')->value('delai_legal_jours');

        if (!$delaiMinimal) {
            return false;
        }

        return today()->lte($this->date_jugement->copy()->addDays($delaiMinimal));
    }

    // ─────────────────────────────────────────
    // ACCESSEURS
    // ─────────────────────────────────────────

    /**
     * Nombre de jours restants avant expiration du délai de recours.
     * Retourne 0 si définitif, null si aucun type de recours configuré,
     * ou un entier négatif si le délai est dépassé.
     */
    public function getDelaiRecoursRestantAttribute(): ?int
    {
        if ($this->est_definitif) {
            return 0;
        }

        $premierRecours = TypeRecours::orderBy('delai_legal_jours')->first();
        if (!$premierRecours) {
            return null;
        }

        $dateLimite = $this->date_jugement->copy()->addDays($premierRecours->delai_legal_jours);

        // diffInDays avec false → négatif si dateLimite est passée
        return today()->diffInDays($dateLimite, false);
    }

    /**
     * Libellé du statut de recours pour affichage dans les vues.
     */
    public function getStatutRecoursLabelAttribute(): string
    {
        if ($this->est_definitif) {
            return 'نهائي';
        }

        $dernierRecours = $this->recours()
            ->with('typeRecours')
            ->latest('date_recours')
            ->first();

        if (!$dernierRecours) {
            $restant = $this->delai_recours_restant;

            if ($restant === null) {
                return 'غير مُهيأ';
            }

            if ($restant < 0) {
                return 'انتهت مهلة الطعن';
            }

            if ($restant === 0) {
                return 'تنتهي المهلة اليوم';
            }

            return "المهلة المتبقية: {$restant} يوم";
        }

        return $dernierRecours->typeRecours->type_recours ?? 'تم تقديم الطعن';
    }

    public function getActivitylogOptions(): LogOptions { 
        return LogOptions::defaults() 
            ->logOnly([ 
                'id_dossier_tribunal', 
                'id_juge', 
                'date_jugement', 
                'contenu_dispositif', 
                'est_definitif', 
                'created_by', 
            ]) 
            ->logOnlyDirty() 
            ->dontSubmitEmptyLogs()
            ->useLogName('jugements');

    }
}