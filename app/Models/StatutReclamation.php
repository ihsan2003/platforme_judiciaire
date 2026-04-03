<?php
// app/Models/StatutReclamation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutReclamation extends Model
{
    use HasFactory;

    protected $table = 'statut_reclamations';
    
    protected $fillable = ['statut_reclamation'];

    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'id_statut_reclamation');
    }
}