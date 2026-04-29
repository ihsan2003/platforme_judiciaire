<?php
// app/Models/Partie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partie extends Model
{
    use HasFactory;

    protected $table = 'parties';
    
    protected $fillable = [
        'nom_partie',
        'type_personne',
        'identifiant_unique',
        'telephone',
        'email',
        'adresse',
        'est_entraide',
        'id_avocat',
    ];

    protected $casts = [
        'est_entraide' => 'boolean',  
    ];

    public function dossiers()
    {
        return $this->belongsToMany(DossierJudiciaire::class, 'dossier_parties', 'id_partie', 'id_dossier')
                    ->withPivot(['id_type_partie', 'id_avocat', 'date_entree'])
                    ->withTimestamps();
    }

    public function jugements()
    {
        return $this->belongsToMany(Jugement::class, 'jugement_parties', 'id_partie', 'id_jugement')
                    ->withPivot(['id_position_institution', 'montant_condamne'])
                    ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'id_partie');
    }

    public function avocat()
    {
        return $this->belongsTo(Avocat::class, 'id_avocat');
    }

    public function estInstitutionDansDossier($dossierId): bool
    {
        return (bool) $this->est_entraide;
    }
}