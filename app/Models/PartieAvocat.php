<?php
// app/Models/PartieAvocat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * Affectation d'un avocat à une partie.
 *
 * id_dossier NULL = tous les dossiers de la partie ; id_degre NULL = tous les degrés.
 */
class PartieAvocat extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'partie_avocats';

    protected $fillable = [
        'id_partie',
        'id_avocat',
        'id_dossier',
        'id_degre',
    ];

    public function partie()
    {
        return $this->belongsTo(Partie::class, 'id_partie');
    }

    public function avocat()
    {
        return $this->belongsTo(Avocat::class, 'id_avocat');
    }

    public function dossier()
    {
        return $this->belongsTo(DossierJudiciaire::class, 'id_dossier');
    }

    public function degre()
    {
        return $this->belongsTo(DegreeJuridiction::class, 'id_degre');
    }

    /** Affectation valable pour tous les dossiers de la partie. */
    public function estGenerale(): bool
    {
        return $this->id_dossier === null;
    }

    /** Libellé de la portée : « الدرجة الأولى », « جميع الدرجات »… */
    public function getLibelleDegreAttribute(): string
    {
        return $this->degre?->degre_juridiction ?? 'جميع الدرجات';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id_partie',
                'id_avocat',
                'id_dossier',
                'id_degre',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('partieAvocats');
    }
}
