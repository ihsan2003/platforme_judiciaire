<?php
// app/Models/Recours.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recours extends Model
{
    use HasFactory;

    protected $table = 'recours';
    
    protected $fillable = [
        'id_jugement',
        'id_dossier_tribunal',
        'id_dossier_recours',
        'id_type_recours',
        'date_recours',
        'motifs'
    ];

    protected $casts = [
        'date_recours' => 'date'
    ];

    public function jugement()
    {
        return $this->belongsTo(Jugement::class, 'id_jugement');
    }

    public function dossierTribunal()
    {
        return $this->belongsTo(DossierTribunal::class, 'id_dossier_tribunal');
    }

    public function dossierRecours()
    {
        return $this->belongsTo(DossierJudiciaire::class, 'id_dossier_recours');
    }

    public function typeRecours()
    {
        return $this->belongsTo(TypeRecours::class, 'id_type_recours');
    }

    public function getEstDansDelaisAttribute(): bool
    {
        $dateLimite = $this->jugement->date_jugement->addDays($this->typeRecours->delai_legal_jours);
        return $this->date_recours <= $dateLimite;
    }
}