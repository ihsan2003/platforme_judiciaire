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

    public function scopeNonSoldes($query)
    {
        return $query->whereRaw('montant_condamne > montant_paye');
    }
}