<?php
// app/Models/Avertissement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Avertissement extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'avertissements';

    protected $fillable = [
        'objet_litige',
        'id_type_affaire',
        'id_tribunal',
        'id_province',
        'id_statut_avertissement',
        'action_service',
        'commentaire',
        'id_demandeur',
        'id_defendeur',
        'created_by',
    ];

    public function typeAffaire()
    {
        return $this->belongsTo(TypeAffaire::class, 'id_type_affaire');
    }

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'id_tribunal');
    }

    public function statut()
    {
        return $this->belongsTo(StatutAvertissement::class, 'id_statut_avertissement');
    }

    /** المدينة */
    public function province()
    {
        return $this->belongsTo(Province::class, 'id_province');
    }

    /** المدعي */
    public function demandeur()
    {
        return $this->belongsTo(Partie::class, 'id_demandeur');
    }

    /** المدعى عليه */
    public function defendeur()
    {
        return $this->belongsTo(Partie::class, 'id_defendeur');
    }

    /** Jugements rendus dans cet avertissement (le plus récent d'abord). */
    public function jugements()
    {
        return $this->hasMany(AvertissementJugement::class, 'id_avertissement')
                    ->orderByDesc('date_jugement');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        // Statut par défaut « في طور الإجراء » (comme pour les dossiers)
        static::creating(function ($avertissement) {
            if (! $avertissement->id_statut_avertissement) {
                $avertissement->id_statut_avertissement = StatutAvertissement::idDefaut();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('avertissements');
    }
}
