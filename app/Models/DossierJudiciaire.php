<?php
// app/Models/DossierJudiciaire.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DossierJudiciaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dossier_judiciaires';
    
    protected $fillable = [
        'numero_dossier_interne',
        'numero_dossier_tribunal',
        'id_type_affaire',
        'id_statut_dossier',
        'date_ouverture',
        'date_cloture',
        'created_by'
    ];

    protected $casts = [
        'date_ouverture' => 'date',
        'date_cloture' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relations
    public function typeAffaire()
    {
        return $this->belongsTo(TypeAffaire::class, 'id_type_affaire');
    }

    public function statut()
    {
        return $this->belongsTo(StatutDossier::class, 'id_statut_dossier');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'dossier_parties', 'id_dossier', 'id_partie')
                    ->withPivot(['id_type_partie', 'id_avocat', 'est_institution', 'date_entree'])
                    ->withTimestamps();
    }

    public function tribunaux()
    {
        return $this->belongsToMany(Tribunal::class, 'dossier_tribunaux', 'id_dossier', 'id_tribunal')
                    ->withPivot(['id_degre', 'date_debut', 'date_fin'])
                    ->withTimestamps();
    }

    public function dossierTribunaux()
    {
        return $this->hasMany(DossierTribunal::class, 'id_dossier');
    }

    public function audiences()
    {
        return $this->hasManyThrough(Audience::class, DossierTribunal::class, 'id_dossier', 'id_dossier_tribunal');
    }

    public function jugements()
    {
        return $this->hasManyThrough(Jugement::class, DossierTribunal::class, 'id_dossier', 'id_dossier_tribunal');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'id_dossier');
    }

    public function recours()
    {
        return $this->hasMany(Recours::class, 'id_dossier_recours');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_dossier');
    }

    // Accesseurs
    public function getEstActifAttribute(): bool
    {
        return $this->statut->statut_dossier !== 'Clôturé';
    }

    public function getDureeTraitementAttribute(): ?int
    {
        if ($this->date_cloture) {
            return $this->date_cloture->diffInDays($this->date_ouverture);
        }
        return null;
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->whereHas('statut', function($q) {
            $q->where('statut_dossier', '!=', 'Clôturé');
        });
    }

    public function scopeParType($query, $typeId)
    {
        return $query->where('id_type_affaire', $typeId);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_ouverture', [$debut, $fin]);
    }
}