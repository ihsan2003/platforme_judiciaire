<?php
// app/Models/Audience.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audience extends Model
{
    use HasFactory;

    protected $table = 'audiences';
    
    protected $fillable = [
        'id_dossier_tribunal',
        'id_type_audience',
        'id_juge',
        'presence_demandeur',
        'presence_defendeur',
        'date_audience',
        'date_prochaine_audience',
        'resultat_audience',
        'actions_demandees'
    ];

    protected $casts = [
        'presence_demandeur' => 'boolean',
        'presence_defendeur' => 'boolean',
        'date_audience' => 'date',
        'date_prochaine_audience' => 'date'
    ];

    public function dossierTribunal()
    {
        return $this->belongsTo(DossierTribunal::class, 'id_dossier_tribunal');
    }

    public function typeAudience()
    {
        return $this->belongsTo(TypeAudience::class, 'id_type_audience');
    }

    public function juge()
    {
        return $this->belongsTo(Juge::class, 'id_juge');
    }

    public function getEstPasseeAttribute(): bool
    {
        return $this->date_audience->isPast();
    }

    public function getEstTodayAttribute(): bool
    {
        return $this->date_audience->isToday();
    }
}