<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatutDossierHistorique extends Model
{
    protected $table = 'statut_dossier_historiques';

    protected $fillable = [
        'id_dossier',
        'id_statut_dossier',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin'   => 'datetime',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierJudiciaire::class, 'id_dossier');
    }

    public function statut()
    {
        return $this->belongsTo(StatutDossier::class, 'id_statut_dossier');
    }
}