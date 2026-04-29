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

    // ─────────────────────────────────────────
    // RÈGLES MÉTIER
    // ─────────────────────────────────────────

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
     * RG — Crée la prochaine instance judiciaire selon la transition souhaitée.
     *
     * @param  string  $libelleDegreDestination  Ex : 'Appel', 'Cassation'
     * @return static|null  La nouvelle instance, ou null si le degré est introuvable
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
        $this->update(['date_fin' => now()]);

        // Ouvrir la nouvelle instance sur le même tribunal
        return self::create([
            'id_dossier'  => $this->id_dossier,
            'id_tribunal' => $this->id_tribunal,
            'id_degre'    => $degre->id,
            'date_debut'  => now(),
            'date_fin'    => null,
        ]);
    }

    /**
     * RG — Indique si cette instance est encore ouverte (sans date de fin).
     */
    public function estOuverte(): bool
    {
        return is_null($this->date_fin);
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
}