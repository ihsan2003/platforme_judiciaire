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
            str_contains($this->statut_dossier, 'طور الاستئناف') => 'info',
            str_contains($this->statut_dossier, 'طور النقض')     => 'dark',
            str_contains($this->statut_dossier, 'إعادة فتح')      => 'warning',
            str_contains($this->statut_dossier, 'قيد التنفيذ')    => 'warning',
            str_contains($this->statut_dossier, 'تم التنفيذ')     => 'success',
            str_contains($this->statut_dossier, 'تم الحكم')       => 'info',
            str_contains($this->statut_dossier, 'موقوف')          => 'danger',
            str_contains($this->statut_dossier, 'حفظ')            => 'secondary',
            str_contains($this->statut_dossier, 'مغلق')           => 'secondary',
            str_contains($this->statut_dossier, 'جاري')           => 'primary',
            default                                                => 'primary',
        };
    }
}