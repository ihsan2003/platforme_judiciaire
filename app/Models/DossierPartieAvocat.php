<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierPartieAvocat extends Model
{
    protected $table = 'dossier_partie_avocats';

    protected $fillable = [
        'id_dossier_partie',
        'id_avocat',
        'id_degre',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    public function dossierPartie()
    {
        return $this->belongsTo(DossierPartie::class, 'id_dossier_partie');
    }

    public function avocat()
    {
        return $this->belongsTo(Avocat::class, 'id_avocat');
    }

    /** NULL = avocat présent durant tout le processus du dossier. */
    public function degre()
    {
        return $this->belongsTo(DegreeJuridiction::class, 'id_degre');
    }
}
