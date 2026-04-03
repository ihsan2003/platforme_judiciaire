<?php
// app/Models/Province.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'provinces';
    
    protected $fillable = ['province', 'id_region'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'id_region');
    }

    public function tribunaux()
    {
        return $this->hasMany(Tribunal::class, 'id_province');
    }
}