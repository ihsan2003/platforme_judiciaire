<?php
// app/Models/ReclamationJugement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Jugement rattaché à une réclamation (شكاية).
 *
 * Même version légère que AvertissementJugement :
 * pas de finances, d'exécution ni de recours.
 */
class ReclamationJugement extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'reclamation_jugements';

    protected $fillable = [
        'id_reclamation',
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

    public function reclamation()
    {
        return $this->belongsTo(Reclamation::class, 'id_reclamation');
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

    /** Jugement définitif qui n'est pas préparatoire (un حكم تمهيدي ne clôt jamais l'instance). */
    public function scopeFinalDefinitif($query)
    {
        $idPrep = TypeJugement::idPreparatoire();

        return $query->where('est_definitif', true)
            ->when($idPrep, fn ($q) => $q->where('id_type_jugement', '!=', $idPrep));
    }

    public function estFinalDefinitif(): bool
    {
        return $this->est_definitif && ! $this->estPreparatoire();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('reclamation_jugements');
    }
}
