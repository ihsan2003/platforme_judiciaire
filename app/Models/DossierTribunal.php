<?php
// app/Models/DossierTribunal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierTribunal extends Model
{
    use HasFactory;

    protected $table = 'dossier_tribunaux';

    protected $fillable = [
        'id_dossier',
        'id_tribunal',
        'id_degre',
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

    /**
     * Audiences appartenant UNIQUEMENT à cette instance judiciaire.
     * Jamais mélangées avec d'autres degrés.
     */
    public function audiences()
    {
        return $this->hasMany(Audience::class, 'id_dossier_tribunal');
    }

    /**
     * Jugements appartenant UNIQUEMENT à cette instance judiciaire.
     * Un seul jugement par instance est autorisé (voir peutAvoirJugement).
     */
    public function jugements()
    {
        return $this->hasMany(Jugement::class, 'id_dossier_tribunal');
    }

    // ─────────────────────────────────────────
    // RÈGLES MÉTIER — CYCLE DE VIE PAR INSTANCE
    // ─────────────────────────────────────────

    /**
     * RG — Retourne l'audience de type "الحكم" de CETTE instance uniquement.
     * Cette audience est unique par instance et conditionne la création du jugement.
     */
    public function audienceHoukm(): ?Audience
    {
        return $this->audiences()
            ->whereHas('typeAudience', fn($q) => $q->where('type_audience', 'الحكم'))
            ->latest('date_audience')
            ->first();
    }

    /**
     * RG — Un jugement ne peut être rendu que si :
     * 1. Une audience de type "الحكم" existe dans CETTE instance
     * 2. Il n'existe pas déjà un jugement dans CETTE instance
     */
    public function peutAvoirJugement(): bool
    {
        if ($this->audienceHoukm() === null) {
            return false;
        }
        // Un seul jugement par instance
        return $this->jugements()->doesntExist();
    }

    /**
     * RG — Vérifie que la date proposée correspond à l'audience الحكم de cette instance.
     * Retourne null si OK, sinon un message d'erreur.
     */
    public function validerDateJugement(string $dateProposee): ?string
    {
        $audienceHoukm = $this->audienceHoukm();

        if (! $audienceHoukm) {
            return 'Aucune audience de type "الحكم" n\'existe dans cette instance.';
        }

        $dateAttendue = $audienceHoukm->date_audience->toDateString();

        if ($dateProposee !== $dateAttendue) {
            return "La date du jugement doit correspondre à la date de l'audience \"الحكم\" ({$audienceHoukm->date_audience->format('d/m/Y')}).";
        }

        if ($audienceHoukm->date_audience->isFuture()) {
            return "La date du jugement ne peut pas être dans le futur.";
        }

        return null; // OK
    }

    /**
     * RG — Indique si cette instance est encore ouverte (sans date de fin).
     */
    public function estOuverte(): bool
    {
        return is_null($this->date_fin);
    }

    /**
     * RG — Indique si cette instance a déjà un jugement rendu.
     */
    public function aUnJugement(): bool
    {
        return $this->jugements()->exists();
    }

    /**
     * RG — Retourne le jugement de cette instance (il ne peut y en avoir qu'un).
     */
    public function leJugement(): ?Jugement
    {
        return $this->jugements()->first();
    }

    /**
     * RG — Retourne le libellé du degré de cette instance.
     */
    public function libelleDegreJuridiction(): string
    {
        return $this->degre?->degre_juridiction ?? '—';
    }

    /**
     * RG — Retourne l'ordre du degré (1 = premier degré, 2 = appel, 3 = cassation).
     */
    public function ordreDegreJuridiction(): int
    {
        return $this->degre?->ordre ?? 0;
    }

    /**
     * RG — Crée l'instance judiciaire suivante dans la même province.
     * Clôture l'instance courante et ouvre la suivante avec le degré cible.
     *
     * @param  string  $libelleDegreDestination  Ex : 'استئناف', 'نقض'
     * @return static|null  La nouvelle instance, ou null si le degré est introuvable
     */
    public function creerInstanceSuivante(string $libelleDegreDestination): ?self
    {
        $degre = DegreeJuridiction::whereRaw('LOWER(degre_juridiction) LIKE ?', [
            '%' . strtolower($libelleDegreDestination) . '%'
        ])->first();

        if (! $degre) {
            return null;
        }

        // Clôturer l'instance courante
        $this->update(['date_fin' => today()->toDateString()]);

        // Chercher le tribunal du degré cible dans la même province
        $idTribunalSuivant = $this->trouverTribunalDuDegre($degre);

        return self::create([
            'id_dossier'  => $this->id_dossier,
            'id_tribunal' => $idTribunalSuivant,
            'id_degre'    => $degre->id,
            'date_debut'  => today()->toDateString(),
            'date_fin'    => null,
        ]);
    }

    /**
     * Trouve le tribunal du degré cible dans la même province que l'instance courante.
     * Fallback : garde le même tribunal si aucun n'est trouvé.
     */
    public function trouverTribunalDuDegre(DegreeJuridiction $degreeCible): int
    {
        $tribunalOrigine = $this->tribunal()->with('province')->first();
        $provinceId      = $tribunalOrigine?->id_province;

        if ($provinceId) {
            $tribunalCible = Tribunal::where('id_province', $provinceId)
                ->where('id_degre', $degreeCible->id)
                ->first();

            if ($tribunalCible) {
                return $tribunalCible->id;
            }
        }

        return $this->id_tribunal;
    }

    /**
     * RG — Vérifie si un appel a été déposé dans le délai légal
     * à partir du jugement le plus récent de cette instance.
     */
    public function appelDansDelai(int $delaiJours): bool
    {
        $dernierJugement = $this->jugements()
            ->latest('date_jugement')
            ->first();

        if (! $dernierJugement) {
            return false;
        }

        $dateLimite = $dernierJugement->date_jugement->addDays($delaiJours);

        return $this->jugements()
            ->whereHas('recours', fn($q) =>
                $q->where('date_recours', '<=', $dateLimite)
                  ->whereHas('typeRecours', fn($q) =>
                      $q->whereRaw("LOWER(type_recours) LIKE '%appel%'")
                  )
            )
            ->exists();
    }

    /**
     * RG — Résumé du statut de cette instance pour l'affichage.
     * Retourne un tableau [label, couleur_bootstrap].
     */
    public function statutInstance(): array
    {
        $jugement = $this->leJugement();

        if (! $this->estOuverte()) {
            if ($jugement?->recours()->exists()) {
                return ['Clôturée — recours déposé', 'secondary'];
            }
            return ['Clôturée', 'secondary'];
        }

        if ($jugement) {
            return ['Jugement rendu', 'warning'];
        }

        if ($this->audienceHoukm()) {
            return ['Audience الحكم tenue', 'info'];
        }

        return ['En cours', 'success'];
    }
}