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
        'date_fin' => 'date'
    ];

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
}