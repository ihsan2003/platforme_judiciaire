<?php
// app/Models/Jugement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jugement extends Model
{
    use HasFactory;

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

        return now()->lte($this->date_jugement->copy()->addDays($this->delaiMinimal));
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
        return now()->diffInDays($dateLimite, false);
    }

    /**
     * Libellé du statut de recours pour affichage dans les vues.
     */
    public function getStatutRecoursLabelAttribute(): string
    {
        if ($this->est_definitif) {
            return 'Définitif';
        }

        $dernierRecours = $this->recours()->with('typeRecours')->latest('date_recours')->first();

        if (!$dernierRecours) {
            $restant = $this->delai_recours_restant;

            if ($restant === null) return 'Non configuré';
            if ($restant < 0)     return 'Délai expiré';
            if ($restant === 0)   return 'Expire aujourd\'hui';

            return "Délai : {$restant} j restants";
        }

        return $dernierRecours->typeRecours->type_recours ?? 'Recours déposé';
    }
}