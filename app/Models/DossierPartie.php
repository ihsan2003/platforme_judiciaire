<?php
// app/Models/DossierPartie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierPartie extends Model
{
    use HasFactory;

    protected $table = 'dossier_parties';
    
    protected $fillable = [
        'id_dossier',
        'id_partie',
        'id_type_partie',
        'date_entree'
    ];

    protected $casts = [
        'date_entree' => 'date'
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierJudiciaire::class, 'id_dossier');
    }

    public function partie()
    {
        return $this->belongsTo(Partie::class, 'id_partie');
    }

    public function typePartie()
    {
        return $this->belongsTo(TypePartie::class, 'id_type_partie');
    }

    public function avocat()
    {
        return $this->belongsTo(Avocat::class, 'id_avocat');
    }
}