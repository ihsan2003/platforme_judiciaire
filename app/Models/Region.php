<?php
// app/Models/Region.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regions';
    
    protected $fillable = ['region'];

    public function provinces()
    {
        return $this->hasMany(Province::class, 'id_region');
    }
}