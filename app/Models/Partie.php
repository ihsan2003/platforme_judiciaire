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
        'adresse'
    ];

    public function dossiers()
    {
        return $this->belongsToMany(DossierJudiciaire::class, 'dossier_parties', 'id_partie', 'id_dossier')
                    ->withPivot(['id_type_partie', 'id_avocat', 'est_institution', 'date_entree'])
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

    public function getEstInstitutionDansDossierAttribute($dossierId): bool
    {
        $dossierPartie = $this->dossiers()
            ->where('dossier_id', $dossierId)
            ->first();
        
        return $dossierPartie ? (bool)$dossierPartie->pivot->est_institution : false;
    }
}