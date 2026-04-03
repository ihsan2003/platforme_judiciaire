<?php
// app/Models/TypeReclamant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeReclamant extends Model
{
    use HasFactory;

    protected $table = 'type_reclamants';
    
    protected $fillable = ['type_reclamant'];

    public function reclamants()
    {
        return $this->hasMany(Reclamant::class, 'id_type_reclamant');
    }
}