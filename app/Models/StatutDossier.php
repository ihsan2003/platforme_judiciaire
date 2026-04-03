<?php
// app/Models/StatutDossier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutDossier extends Model
{
    use HasFactory;

    protected $table = 'statut_dossiers';

    protected $fillable = ['statut_dossier'];

    // ─── Relations ────────────────────────────────────────────────────────
    public function dossiers()
    {
        return $this->hasMany(DossierJudiciaire::class, 'id_statut_dossier');
    }

    // ─── Accesseur utilitaire (pour éviter le match() partout dans les vues) ──
    public function getCouleurBootstrapAttribute(): string
    {
        return match(true) {
            str_contains($this->statut_dossier, 'cours')   => 'warning',
            str_contains($this->statut_dossier, 'Clôturé') => 'secondary',
            str_contains($this->statut_dossier, 'Jugé')    => 'info',
            str_contains($this->statut_dossier, 'Exécuté') => 'success',
            default                                        => 'primary',
        };
    }
}
