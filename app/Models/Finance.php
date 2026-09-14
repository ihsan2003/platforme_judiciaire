<?php
// app/Models/Finance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    use HasFactory;

    protected $table = 'finances';
    
    protected $fillable = [
        'id_jugement',
        'montant_reclame_demandeur',
        'montant_reclame_defendeur',
        'montant_condamne',
        'montant_paye',
        'date_paiement',
        'statut_paiement'
    ];

    protected $casts = [
        'montant_reclame_demandeur' => 'decimal:2',
        'montant_reclame_defendeur' => 'decimal:2',
        'montant_condamne' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_paiement' => 'date'
    ];

    public function jugement()
    {
        return $this->belongsTo(Jugement::class, 'id_jugement');
    }

    public function getMontantRestantAttribute(): float
    {
        return ($this->montant_condamne ?? 0) - ($this->montant_paye ?? 0);
    }

    public function getEstSoldeAttribute(): bool
    {
        return $this->montant_restant <= 0;
    }

    /**
     * Vrai si cette finance est bien celle du DERNIER jugement valide de son
     * dossier (plus haut degré de juridiction, puis date la plus récente) —
     * c-à-d celle prise en compte dans "الخلاصة المالية" du dashboard.
     * Sert à avertir quand un paiement est saisi sur un jugement dépassé
     * (remplacé par un appel/une cassation) : ce paiement ne sera alors pas
     * compté.
     */
    public function getEstFinanceValideAttribute(): bool
    {
        $dossier = $this->jugement?->dossierTribunal?->dossier;

        return $dossier && $dossier->financeValide?->id === $this->id;
    }

    public function scopeNonSoldes($query)
    {
        return $query->whereRaw('montant_condamne > montant_paye');
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function ($finance) {
         
            if ($finance->montant_paye >= $finance->montant_condamne) {
                $finance->statut_paiement = 'مكتمل';
            } elseif ($finance->montant_paye > 0) {
                $finance->statut_paiement = 'جزئي';
            } else {
                $finance->statut_paiement = 'في الانتظار';
            }
        });
    }
}