<?php
// app/Models/Reclamant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reclamant extends Model
{
    use HasFactory;

    protected $table = 'reclamants';
    
    protected $fillable = [
        'nom',
        'id_type_reclamant',
        'telephone',
        'email',
        'adresse'
    ];

    public function typeReclamant()
    {
        return $this->belongsTo(TypeReclamant::class, 'id_type_reclamant');
    }

    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'id_reclamant');
    }
}