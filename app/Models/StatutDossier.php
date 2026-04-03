<?php
// app/Models/StatutDossier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutDossier extends Model
{
    use HasFactory;

    protected $table = 'statut_dossiers';
    
    protected $fillable = ['statut_dossier'];

    public function dossiers()
    {
        return $this->hasMany(DossierJudiciaire::class, 'id_statut_dossier');
    }
}