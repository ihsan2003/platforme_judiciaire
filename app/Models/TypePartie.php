<?php
// app/Models/TypePartie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePartie extends Model
{
    use HasFactory;

    protected $table = 'type_parties';
    
    protected $fillable = ['type_partie'];

    public function dossierParties()
    {
        return $this->hasMany(DossierPartie::class, 'id_type_partie');
    }
}