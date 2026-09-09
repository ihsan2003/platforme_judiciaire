<?php
// app/Models/Reclamation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reclamation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reclamations';
    
    protected $fillable = [
        'id_reclamant',
        'id_type_reclamation',
        'objet',
        'date_reception',
        'id_statut_reclamation',
        'details'
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
}