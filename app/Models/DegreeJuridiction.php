<?php
// app/Models/DegreJuridiction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeJuridiction extends Model
{
    use HasFactory;

    protected $table = 'degre_juridictions';
    
    protected $fillable = ['degre_juridiction', 'ordre'];

    public function dossierTribunaux()
    {
        return $this->hasMany(DossierTribunal::class, 'id_degre');
    }

    public function tribunaux()
    {
        return $this->hasMany(Tribunal::class, 'id_degre');
    }
}