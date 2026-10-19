<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatutReclamationHistorique extends Model
{
    protected $table = 'statut_reclamation_historiques';

    protected $fillable = [
        'id_reclamation',
        'id_statut_reclamation',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin'   => 'datetime',
    ];

    public function reclamation()
    {
        return $this->belongsTo(Reclamation::class, 'id_reclamation');
    }

    public function statut()
    {
        return $this->belongsTo(StatutReclamation::class, 'id_statut_reclamation');
    }
}