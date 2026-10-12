<?php
// app/Models/DossierPartie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DossierPartie extends Model
{
    use HasFactory, LogsActivity;

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

    /**
     * Avocats de la partie dans CE dossier (un ou plusieurs, éventuellement
     * par degré de juridiction : 1ère instance, appel, cassation...).
     */
    public function affectations()
    {
        return $this->hasMany(DossierPartieAvocat::class, 'id_dossier_partie')
                    ->orderByRaw('id_degre IS NOT NULL')
                    ->orderBy('id_degre')
                    ->orderBy('date_debut');
    }

    public function typePartie()
    {
        return $this->belongsTo(TypePartie::class, 'id_type_partie');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_dossier',
                'id_partie',
                'id_type_partie',
                'date_entree',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('dossierParties'); 
    }

}