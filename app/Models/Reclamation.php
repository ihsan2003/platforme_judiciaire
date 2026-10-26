<?php
// app/Models/Reclamation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Reclamation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'reclamations';
    
    protected $fillable = [
        'id_reclamant',
        'id_type_reclamation',
        'objet',
        'date_reception',
        'id_statut_reclamation',
        'details',
        'id_province',
        'id_commune',
        // Champs "dossier" (type شكايات مرتبطة بالمنازعات القضائية)
        'id_degre',
        'id_tribunal',
        'id_type_affaire',
        'id_demandeur',
        'id_defendeur',
    ];

    protected $casts = [
        'date_reception' => 'date'
    ];

    public function reclamant()
    {
        return $this->belongsTo(Reclamant::class, 'id_reclamant');
    }

    public function typeReclamation()
    {
        return $this->belongsTo(TypeReclamation::class, 'id_type_reclamation');
    }

    public function statut()
    {
        return $this->belongsTo(StatutReclamation::class, 'id_statut_reclamation');
    }

    /** الإقليم / العمالة (la région s'obtient via province.region) */
    public function province()
    {
        return $this->belongsTo(Province::class, 'id_province');
    }

    /** الجماعة الترابية */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'id_commune');
    }

    // ── Champs "dossier" ───────────────────────────────────────────────────

    /** درجة التقاضي */
    public function degre()
    {
        return $this->belongsTo(DegreeJuridiction::class, 'id_degre');
    }

    /** المحكمة */
    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'id_tribunal');
    }

    /** الشعبة / نوع القضية */
    public function typeAffaire()
    {
        return $this->belongsTo(TypeAffaire::class, 'id_type_affaire');
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

    /**
     * Cette réclamation est-elle de type « شكايات مرتبطة بالمنازعات القضائية »,
     * c'est-à-dire traitée comme un dossier (degré, tribunal, type d'affaire, parties) ?
     */
    public function estJudiciaire(): bool
    {
        return $this->typeReclamation?->type_reclamation === TypeReclamation::JUDICIAIRE;
    }

    public function historiqueStatuts()
    {
        return $this->hasMany(StatutReclamationHistorique::class, 'id_reclamation');
    }

    // ── Historique des statuts (voir DossierJudiciaire::booted() pour le
    // même mécanisme, avec les explications détaillées) ────────────────
    protected static function booted()
    {
        static::created(function ($reclamation) {
            if ($reclamation->id_statut_reclamation) {
                StatutReclamationHistorique::create([
                    'id_reclamation'        => $reclamation->id,
                    'id_statut_reclamation' => $reclamation->id_statut_reclamation,
                    'date_debut'            => $reclamation->date_reception ?? now(),
                    'date_fin'              => null,
                ]);
            }
        });

        static::updated(function ($reclamation) {
            if (!$reclamation->wasChanged('id_statut_reclamation')) {
                return;
            }

            $ancienStatutId = $reclamation->getOriginal('id_statut_reclamation');
            $maintenant = now();

            if ($ancienStatutId) {
                StatutReclamationHistorique::where('id_reclamation', $reclamation->id)
                    ->where('id_statut_reclamation', $ancienStatutId)
                    ->whereNull('date_fin')
                    ->update(['date_fin' => $maintenant]);
            }

            if ($reclamation->id_statut_reclamation) {
                StatutReclamationHistorique::create([
                    'id_reclamation'        => $reclamation->id,
                    'id_statut_reclamation' => $reclamation->id_statut_reclamation,
                    'date_debut'            => $maintenant,
                    'date_fin'              => null,
                ]);
            }
        });
    }

    public function actions()
    {
        return $this->hasMany(ActionReclamation::class, 'id_reclamation')->orderBy('created_at', 'desc');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'id_reclamation');
    }

    /** Jugements rendus pour cette réclamation (le plus récent d'abord). */
    public function jugements()
    {
        return $this->hasMany(ReclamationJugement::class, 'id_reclamation')
                    ->orderByDesc('date_jugement');
    }


    public function getDerniereActionAttribute()
    {
        return $this->actions()->first();
    }

    public function getDureeTraitementAttribute(): ?int
    {
        $premiereAction = $this->actions()->oldest()->first();
        $derniereAction = $this->actions()->latest()->first();
        
        if ($premiereAction && $derniereAction) {
            return $premiereAction->created_at->diffInDays($derniereAction->created_at);
        }
        
        return $this->created_at->diffInDays(now());
    }

    public function scopeEnAttente($query)
    {
        return $query->whereHas('statut', function($q) {
            $q->whereIn('statut_reclamation', ['Reçue', 'En cours']);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_reclamant',
                'id_type_reclamation',
                'objet',
                'date_reception',
                'id_statut_reclamation',
                'details',
                'id_province',
                'id_commune',
                'id_degre',
                'id_tribunal',
                'id_type_affaire',
                'id_demandeur',
                'id_defendeur',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('reclamations');

    }
}