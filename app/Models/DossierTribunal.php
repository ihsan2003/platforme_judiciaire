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
        $dernierJugement = $this->jugements()
            ->latest('date_jugement')
            ->first();

        if (!$dernierJugement) {
            return false;
        }

        $dateLimite = $dernierJugement->date_jugement->copy()->addDays($delaiJours);

        return $this->jugements()
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
            if ($this->aUnJugement()) {
                return 'Jugement rendu';
            }
            if ($this->audienceHoukm()) {
                return 'En délibéré';
            }
            return 'En cours';
        }

        // Clôturée — chercher la raison
        $jugement = $this->jugements()->with('recours.typeRecours')->latest('date_jugement')->first();

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
        if ($this->aUnJugement()) {
            return 'info';
        }
        if ($this->audienceHoukm()) {
            return 'warning';
        }
        return 'success';
    }
}