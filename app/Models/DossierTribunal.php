<?php
// app/Models/DossierTribunal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DossierTribunal extends Model
{
    use HasFactory, LogsActivity;

    /** Libellé du type d'audience "الحكم" (type_audiences.type_audience). */
    public const TYPE_AUDIENCE_HOUKM = 'الحكم';

    protected $table = 'dossier_tribunaux';

    protected $fillable = [
        'id_dossier',
        'id_tribunal',
        'id_degre',
        'id_commune',
        'date_debut',
        'date_fin'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    // ─────────────────────────────────────────
    // RELATIONS
    // ─────────────────────────────────────────

    public function dossier()
    {
        return $this->belongsTo(DossierJudiciaire::class, 'id_dossier');
    }

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'id_tribunal');
    }

    public function degre()
    {
        return $this->belongsTo(DegreeJuridiction::class, 'id_degre');
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'id_commune');
    }

    public function audiences()
    {
        return $this->hasMany(Audience::class, 'id_dossier_tribunal');
    }

    public function jugements()
    {
        return $this->hasMany(Jugement::class, 'id_dossier_tribunal');
    }

    public function recours()
    {
        return $this->hasMany(Recours::class, 'id_dossier_tribunal');
    }

    // ─────────────────────────────────────────
    // RÈGLES MÉTIER
    // ─────────────────────────────────────────

    /**
     * RG — Retourne l'audience de type "الحكم" de cette instance, ou null.
     * Il ne peut y en avoir qu'une seule par instance.
     */
    public function audienceHoukm(): ?Audience
    {
        return $this->audiences()
            ->whereHas('typeAudience', fn($q) => $q->where('type_audience', 'الحكم'))
            ->latest('date_audience')
            ->first();
    }

    /**
     * RG — Un jugement ne peut être rendu que si une audience
     * de type "الحكم" (délibéré / rendu) a eu lieu dans cette instance.
     */
    public function peutAvoirJugement(): bool
    {
        return $this->audienceHoukm() !== null;
    }

    /**
     * RG — Indique si cette instance possède déjà un jugement.
     * Utilisé par AudienceController pour bloquer les nouvelles audiences
     * après qu'un jugement a été rendu.
     *
     * Note : on vérifie via la relation pour ne pas charger tout le jugement.
     */
    public function aUnJugement(): bool
    {
        return $this->jugements()->exists();
    }

    /**
     * RG — Indique si cette instance possède un jugement sur le fond
     * un jugement préparatoire (حكم تمهيدي) laisse l'instruction se poursuivre.
     */
    public function aUnJugementAuFond(): bool
    {
        return $this->jugements()->auFond()->exists();
    }

    public function jugementAuFond(): ?Jugement
    {
        return $this->jugements()->auFond()->latest('date_jugement')->first();
    }

    /**
     * Dernier jugement préparatoire rendu dans cette instance, ou null.
     */
    public function dernierJugementPreparatoire(): ?Jugement
    {
        return $this->jugements()->preparatoires()->latest('date_jugement')->first();
    }

    /**
     * RG — Un seul حكم تمهيدي peut être enregistré après chaque audience,
     * sauf après une audience de type "الحكم" (où c'est le jugement sur le
     * fond qui est rendu : aucun jugement préparatoire n'y est accepté).
     *
     * Le jugement préparatoire est rattaché à une audience par sa date
     * (date_jugement = date_audience, cf. JugementController::store).
     *
     * @param  string    $date               Date du jugement (Y-m-d)
     * @param  int|null  $ignorerJugementId  Jugement à ignorer (cas d'une modification)
     * @return string|null  Message d'erreur, ou null si la règle est respectée
     *                      (ou s'il n'y a aucune audience à cette date : la
     *                      correspondance date/audience est contrôlée ailleurs).
     */
    public function erreurPreparatoireAuDate(string $date, ?int $ignorerJugementId = null): ?string
    {
        $audiences = $this->audiences()->with('typeAudience')
            ->whereDate('date_audience', $date)
            ->get();

        if ($audiences->isEmpty()) {
            return null;
        }

        $audiencesNonHoukm = $audiences->reject(
            fn($a) => $a->typeAudience?->type_audience === self::TYPE_AUDIENCE_HOUKM
        );

        if ($audiencesNonHoukm->isEmpty()) {
            return 'لا يمكن تسجيل حكم تمهيدي بعد جلسة من نوع "الحكم".';
        }

        $preparatoiresExistants = $this->jugements()
            ->preparatoires()
            ->whereDate('date_jugement', $date)
            ->when($ignorerJugementId, fn($q) => $q->where('id', '!=', $ignorerJugementId))
            ->count();

        if ($preparatoiresExistants >= $audiencesNonHoukm->count()) {
            return 'يوجد بالفعل حكم تمهيدي مسجل بعد هذه الجلسة. لا يمكن تسجيل أكثر من حكم تمهيدي واحد بعد كل جلسة.';
        }

        return null;
    }

    /**
     * Cette audience peut-elle encore recevoir un حكم تمهيدي ?
     * (instance ouverte, pas de jugement sur le fond, audience hors "الحكم",
     * pas de حكم تمهيدي déjà rendu à cette date, et date postérieure au
     * dernier حكم تمهيدي, comme l'exige JugementController::store).
     */
    public function audiencePeutRecevoirPreparatoire(Audience $audience): bool
    {
        if (! $this->estOuverte() || $this->aUnJugementAuFond()) {
            return false;
        }

        $date = $audience->date_audience->toDateString();

        $dernierPrep = $this->dernierJugementPreparatoire();
        if ($dernierPrep && $date <= $dernierPrep->date_jugement->toDateString()) {
            return false;
        }

        return $this->erreurPreparatoireAuDate($date) === null;
    }

    /**
     * Au moins une audience de l'instance peut-elle encore recevoir un حكم تمهيدي ?
     */
    public function peutRecevoirPreparatoire(): bool
    {
        return $this->audiences()->with('typeAudience')->get()
            ->contains(fn($a) => $this->audiencePeutRecevoirPreparatoire($a));
    }

    /**
     * RG — Audience "الحكم" sur laquelle le jugement sur le fond doit être
     * enregistré : la plus récente, et postérieure au dernier jugement
     * préparatoire s'il y en a un (le jugement préparatoire peut avoir été
     * rendu à une audience "الحكم" qui ne doit pas être réutilisée).
     */
    public function audienceHoukmPourJugementAuFond(): ?Audience
    {
        $audience = $this->audienceHoukm();

        if (! $audience) {
            return null;
        }

        $preparatoire = $this->dernierJugementPreparatoire();

        if ($preparatoire && $audience->date_audience->lte($preparatoire->date_jugement)) {
            return null;
        }

        return $audience;
    }

    /**
     * RG — Indique si cette instance est encore ouverte (sans date de fin).
     * Une instance clôturée ne peut plus recevoir d'audiences ni de jugement.
     */
    public function estOuverte(): bool
    {
        return is_null($this->date_fin);
    }

    /**
     * RG — Crée la prochaine instance judiciaire selon le degré demandé.
     * Clôture l'instance courante avant d'ouvrir la nouvelle.
     *
     * @param  string  $libelleDegreDestination  Ex : 'استئناف', 'نقض'
     * @return static|null
     */
    public function creerInstanceSuivante(string $libelleDegreDestination): ?self
    {
        $degre = DegreeJuridiction::whereRaw('LOWER(degre_juridiction) LIKE ?', [
            '%' . strtolower($libelleDegreDestination) . '%'
        ])->first();

        if (!$degre) {
            return null;
        }

        // Clôturer l'instance courante
        $this->update(['date_fin' => now()->toDateString()]);

        // Trouver le tribunal du degré cible dans la même province
        $idTribunal = $this->trouverTribunalDuDegre($degre);

        return self::create([
            'id_dossier'  => $this->id_dossier,
            'id_tribunal' => $idTribunal,
            'id_degre'    => $degre->id,
            'date_debut'  => now()->toDateString(),
            'date_fin'    => null,
        ]);
    }

    /**
     * Trouve le tribunal du degré cible dans la même province que l'instance courante.
     * Fallback : même tribunal si aucun trouvé.
     */
    private function trouverTribunalDuDegre(DegreeJuridiction $degre): int
    {
        $provinceId = $this->tribunal()->with('province')->first()?->id_province;

        if ($provinceId) {
            $tribunal = Tribunal::where('id_province', $provinceId)
                ->where('id_degre', $degre->id)
                ->first();

            if ($tribunal) {
                return $tribunal->id;
            }
        }

        return $this->id_tribunal; // fallback
    }

    /**
     * RG — Vérifie si un appel a été déposé dans le délai légal
     * à partir du jugement le plus récent de cette instance.
     *
     * @param  int  $delaiJours  Délai légal applicable (ex : 30 jours)
     */
    public function appelDansDelai(int $delaiJours): bool
    {
        // Le délai court à partir du jugement sur le fond uniquement.
        $dernierJugement = $this->jugements()
            ->auFond()
            ->latest('date_jugement')
            ->first();

        if (!$dernierJugement) {
            return false;
        }

        $dateLimite = $dernierJugement->date_jugement->copy()->addDays($delaiJours);

        return $this->jugements()
            ->auFond()
            ->whereHas('recours', fn($q) =>
                $q->where('date_recours', '<=', $dateLimite)
                  ->whereHas('typeRecours', fn($q) =>
                      $q->whereRaw("LOWER(type_recours) LIKE '%appel%'")
                  )
            )
            ->exists();
    }

    // ─────────────────────────────────────────
    // ACCESSEURS UTILITAIRES
    // ─────────────────────────────────────────

    /**
     * Retourne le label du statut de cette instance pour les vues.
     */
    public function getStatutLabelAttribute(): string
    {
        if ($this->estOuverte()) {
            if ($this->aUnJugementAuFond()) {
                return 'Jugement rendu';
            }
            if ($this->audienceHoukmPourJugementAuFond()) {
                return 'En délibéré';
            }
            if ($this->dernierJugementPreparatoire()) {
                return 'Jugement préparatoire rendu';
            }
            return 'En cours';
        }

        // Clôturée — chercher la raison (jugement sur le fond)
        $jugement = $this->jugements()->auFond()->with('recours.typeRecours')->latest('date_jugement')->first();

        if ($jugement && $jugement->recours->isNotEmpty()) {
            $typeRecours = $jugement->recours->first()->typeRecours?->type_recours ?? 'Recours';
            return "Clôturée — {$typeRecours}";
        }

        return 'Clôturée';
    }

    /**
     * Retourne la couleur Bootstrap associée au statut de l'instance.
     */
    public function getCouleurStatutAttribute(): string
    {
        if (!$this->estOuverte()) {
            return 'secondary';
        }
        if ($this->aUnJugementAuFond()) {
            return 'info';
        }
        if ($this->audienceHoukmPourJugementAuFond()) {
            return 'warning';
        }
        if ($this->dernierJugementPreparatoire()) {
            return 'primary';
        }
        return 'success';
    }

    public function getDerniereDtAttribute(): ?DossierTribunal
    {
        return $this->dossierTribunaux
            ->sortByDesc(fn($dt) => $dt->degre?->ordre ?? 0)
            ->first();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_dossier',
                'id_tribunal',
                'id_degre',
                'id_commune',
                'date_debut',
                'date_fin',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('dossierTribunaux'); 
    }
}