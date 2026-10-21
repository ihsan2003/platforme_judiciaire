<?php
// app/Models/AvertissementJugement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Jugement rattaché à un avertissement.
 *
 * Version volontairement légère par rapport à Jugement (dossiers) :
 * pas de finances, d'exécution ni de recours.
 */
class AvertissementJugement extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'avertissement_jugements';

    protected $fillable = [
        'id_avertissement',
        'id_juge',
        'id_type_jugement',
        'date_jugement',
        'contenu_dispositif',
        'est_definitif',
        'created_by',
    ];

    protected $casts = [
        'date_jugement' => 'date',
        'est_definitif' => 'boolean',
    ];

    public function avertissement()
    {
        return $this->belongsTo(Avertissement::class, 'id_avertissement');
    }

    public function juge()
    {
        return $this->belongsTo(Juge::class, 'id_juge');
    }

    public function typeJugement()
    {
        return $this->belongsTo(TypeJugement::class, 'id_type_jugement');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function estPreparatoire(): bool
    {
        return $this->id_type_jugement !== null
            && (int) $this->id_type_jugement === (int) TypeJugement::idPreparatoire();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('avertissement_jugements');
    }
}
