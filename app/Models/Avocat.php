<?php
// app/Models/Avocat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avocat extends Model
{
    use HasFactory;

    protected $table = 'avocats';
    
    protected $fillable = [
        'nom_avocat',
        'telephone',
        'email'
    ];

    public function dossierParties()
    {
        return $this->hasMany(DossierPartie::class, 'id_avocat');
    }
}