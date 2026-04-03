<?php
// app/Models/TypeAudience.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeAudience extends Model
{
    use HasFactory;

    protected $table = 'type_audiences';
    
    protected $fillable = ['type_audience'];

    public function audiences()
    {
        return $this->hasMany(Audience::class, 'id_type_audience');
    }
}