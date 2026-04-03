<?php
// app/Models/TypeTribunal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeTribunal extends Model
{
    use HasFactory;

    protected $table = 'type_tribunaux';
    
    protected $fillable = ['tribunal'];

    public function tribunaux()
    {
        return $this->hasMany(Tribunal::class, 'id_type_tribunal');
    }
}