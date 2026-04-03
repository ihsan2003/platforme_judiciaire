<?php
// app/Models/PositionInstitution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionInstitution extends Model
{
    use HasFactory;

    protected $table = 'position_institutions';
    
    protected $fillable = ['position'];

    public function jugementParties()
    {
        return $this->hasMany(JugementPartie::class, 'id_position_institution');
    }
}