<?php
// app/Models/StatutAvertissement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutAvertissement extends Model
{
    use HasFactory;

    public const DEFAUT = 'في طور الإجراء';

    protected $table = 'statut_avertissements';

    protected $fillable = ['statut_avertissement'];

    public function avertissements()
    {
        return $this->hasMany(Avertissement::class, 'id_statut_avertissement');
    }

    public static function idDefaut(): ?int
    {
        return static::where('statut_avertissement', self::DEFAUT)->value('id');
    }

    /** Même palette que StatutDossier (cohérence visuelle des badges). */
    public function getCouleurBootstrapAttribute(): string
    {
        return match(true) {
            // « لم يتم… » doit être testé avant « تم… » (qui en est une sous-chaîne)
            str_contains($this->statut_avertissement, 'لم يتم التوصل بالانذار') => 'warning',
            str_contains($this->statut_avertissement, 'تم التوصل بالانذار')     => 'success',
            str_contains($this->statut_avertissement, 'حفظ')                    => 'secondary',
            default                                                             => 'primary',
        };
    }
}
