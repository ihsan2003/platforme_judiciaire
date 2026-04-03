<?php
// app/Models/TypeAffaire.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeAffaire extends Model
{
    use HasFactory;

    protected $table = 'type_affaires';
    
    protected $fillable = ['affaire'];

    public function dossiers()
    {
        return $this->hasMany(DossierJudiciaire::class, 'id_type_affaire');
    }
}