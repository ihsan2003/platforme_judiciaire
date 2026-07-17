<?php
// app/Models/TypeReclamation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeReclamation extends Model
{
    use HasFactory;

    protected $table = 'type_reclamations';

    protected $fillable = ['type_reclamation'];

    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'id_type_reclamation');
    }
}