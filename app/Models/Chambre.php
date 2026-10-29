<?php
// app/Models/Chambre.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chambre extends Model
{
    use HasFactory;

    protected $table = 'chambres';

    protected $fillable = [
        'id_tribunal',
        'id_type_chambre',
        'numero',
        'chambre',
    ];

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'id_tribunal');
    }

    public function type()
    {
        return $this->belongsTo(TypeChambre::class, 'id_type_chambre');
    }

    public function dossierTribunaux()
    {
        return $this->hasMany(DossierTribunal::class, 'id_chambre');
    }
}
