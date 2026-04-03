<?php
// app/Models/TypeRecours.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeRecours extends Model
{
    use HasFactory;

    protected $table = 'type_recours';
    
    protected $fillable = ['type_recours', 'delai_legal_jours'];

    public function recours()
    {
        return $this->hasMany(Recours::class, 'id_type_recours');
    }
}