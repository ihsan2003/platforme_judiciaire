<?php
// app/Models/TypeStructure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeStructure extends Model
{
    use HasFactory;

    protected $table = 'type_structures';
    
    protected $fillable = ['type_structure'];

    public function structures()
    {
        return $this->hasMany(Structure::class, 'id_type_structure');
    }
}