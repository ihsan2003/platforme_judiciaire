<?php
// app/Models/Avertissement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Avertissement extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'avertissements';

    protected $fillable = [
        'objet_litige',
        'id_type_affaire',
        'id_tribunal',
        'id_province',
        'id_commune',
        'id_statut_avertissement',
        'date_avertissement',
        'action_service',
        'commentaire',
        'id_demandeur',
        'id_defendeur',
        'created_by',
    ];

    protected $casts = [
        'date_avertissement' => 'date',
    ];

    public function typeAffaire()
    {
        return $this->belongsTo(TypeAffaire::class, 'id_type_affaire');
    }

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'id_tribunal');
    }

    public function statut()
    {
        return $this->belongsTo(StatutAvertissement::class, 'id_statut_avertissement');
    }

    /** المدينة */
    public function province()
    {
        return $this->belongsTo(Province::class, 'id_province');
    }

    /** الجماعة الترابية */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'id_commune');
    }

    /** المدعي */
    public function demandeur()
    {
        return $this->belongsTo(Partie::class, 'id_demandeur');
    }

    /** المدعى عليه */
    public function defendeur()
    {
        return $this->belongsTo(Partie::class, 'id_defendeur');
    }

    /** Jugements rendus dans cet avertissement (le plus récent d'abord). */
    public function jugements()
    {
        return $this->hasMany(AvertissementJugement::class, 'id_avertissement')
                    ->orderByDesc('date_jugement');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─────────────────────────────────────────
    // RG — Jugement final définitif => statut « حفظ »
    // ─────────────────────────────────────────

    /** Un jugement final définitif a-t-il été rendu pour cet avertissement ? */
    public function aJugementFinalDefinitif(): bool
    {
        return $this->jugements()->getQuery()->reorder()->finalDefinitif()->exists();
    }

    /**
     * Applique « حفظ » si un jugement final définitif existe. Idempotent.
     */
    public function appliquerClassementSiJugementFinalDefinitif(): void
    {
        $idClassement = StatutAvertissement::idClassement();

        if (! $idClassement) {
            \Log::warning("Statut 'حفظ' introuvable — classement annulé pour l'avertissement #{$this->id}");
            return;
        }

        if ((int) $this->id_statut_avertissement !== (int) $idClassement) {
            $this->update(['id_statut_avertissement' => $idClassement]);
        }
    }

    /** Le statut est verrouillé tant qu'un jugement final définitif existe. */
    public function statutVerrouille(): bool
    {
        return $this->exists && $this->aJugementFinalDefinitif();
    }

    protected static function booted()
    {
        // Garde-fou : quel que soit le point d'entrée (formulaire, tinker, import…),
        // aucun autre statut que « حفظ » ne peut être appliqué après un jugement final définitif.
        static::updating(function (Avertissement $avertissement) {
            if (! $avertissement->isDirty('id_statut_avertissement') || ! $avertissement->aJugementFinalDefinitif()) {
                return;
            }

            $idClassement = StatutAvertissement::idClassement();

            if ($idClassement && (int) $avertissement->id_statut_avertissement !== (int) $idClassement) {
                $avertissement->id_statut_avertissement = $idClassement;
            }
        });

        // Statut par défaut « في طور الإجراء » (comme pour les dossiers)
        static::creating(function ($avertissement) {
            if (! $avertissement->id_statut_avertissement) {
                $avertissement->id_statut_avertissement = StatutAvertissement::idDefaut();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('avertissements');
    }
}
