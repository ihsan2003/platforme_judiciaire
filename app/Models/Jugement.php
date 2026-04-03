<?php
// app/Models/Jugement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jugement extends Model
{
    use HasFactory;

    protected $table = 'jugements';
    
    protected $fillable = [
        'id_dossier_tribunal',
        'id_juge',
        'date_jugement',
        'contenu_dispositif',
        'est_definitif',
        'created_by'
    ];

    protected $casts = [
        'date_jugement' => 'date',
        'est_definitif' => 'boolean'
    ];

    public function dossierTribunal()
    {
        return $this->belongsTo(DossierTribunal::class, 'id_dossier_tribunal');
    }

    public function juge()
    {
        return $this->belongsTo(Juge::class, 'id_juge');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'jugement_parties', 'id_jugement', 'id_partie')
                    ->withPivot(['id_position_institution', 'montant_condamne'])
                    ->withTimestamps();
    }

    public function finance()
    {
        return $this->hasOne(Finance::class, 'id_jugement');
    }

    public function recours()
    {
        return $this->hasMany(Recours::class, 'id_jugement');
    }

    public function executions()
    {
        return $this->hasMany(Execution::class, 'id_jugement');
    }

    public function getDelaiRecoursRestantAttribute(): ?int
    {
        if ($this->est_definitif) {
            return 0;
        }

        $premierRecours = TypeRecours::orderBy('delai_legal_jours')->first();
        if (!$premierRecours) {
            return null;
        }

        $dateLimite = $this->date_jugement->addDays($premierRecours->delai_legal_jours);
        return now()->diffInDays($dateLimite, false);
    }
}