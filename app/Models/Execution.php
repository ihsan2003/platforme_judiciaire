<?php
// app/Models/Execution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Execution extends Model
{
    use HasFactory;

    protected $table = 'executions';
    
    protected $fillable = [
        'id_jugement',
        'numero_dossier_execution',
        'date_notification',
        'statut_execution',
        'date_execution',
        'responsable_id'
    ];

    protected $casts = [
        'date_notification' => 'date',
        'date_execution' => 'date'
    ];

    public function jugement()
    {
        return $this->belongsTo(Jugement::class, 'id_jugement');
    }

    public function statut()
    {
        return $this->belongsTo(StatutExecution::class, 'statut_execution');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function finance()
    {
        return $this->hasOneThrough(
            Finance::class,
            Jugement::class,
            'id',
            'id_jugement',
            'id_jugement',
            'id'
        );
    }
}