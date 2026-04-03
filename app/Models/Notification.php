<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    
    protected $fillable = [
        'id_utilisateur',
        'type_notification',
        'message',
        'id_dossier',
        'id_reclamation',
        'est_lue',
        'date_notification',
        'date_lecture'
    ];

    protected $casts = [
        'est_lue' => 'boolean',
        'date_notification' => 'datetime',
        'date_lecture' => 'datetime'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }

    public function dossier()
    {
        return $this->belongsTo(DossierJudiciaire::class, 'id_dossier');
    }

    public function reclamation()
    {
        return $this->belongsTo(Reclamation::class, 'id_reclamation');
    }

    public function marquerCommeLue()
    {
        $this->update([
            'est_lue' => true,
            'date_lecture' => now()
        ]);
    }

    public function scopeNonLues($query)
    {
        return $query->where('est_lue', false);
    }

    public function scopePourUtilisateur($query, $userId)
    {
        return $query->where('id_utilisateur', $userId);
    }
}