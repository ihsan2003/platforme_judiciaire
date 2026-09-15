<?php
// app/Models/JugementPartie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class JugementPartie extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'jugement_parties';
    
    protected $fillable = [
        'id_jugement',
        'id_partie',
        'id_position_institution',
        'montant_condamne'
    ];

    protected $casts = [
        'montant_condamne' => 'decimal:2'
    ];

    public function jugement()
    {
        return $this->belongsTo(Jugement::class, 'id_jugement');
    }

    public function partie()
    {
        return $this->belongsTo(Partie::class, 'id_partie');
    }

    public function positionInstitution()
    {
        return $this->belongsTo(PositionInstitution::class, 'id_position_institution');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_jugement',
                'id_partie',
                'id_position_institution',
                'montant_condamne',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('jugementParties'); 
    }
}