<?php
// app/Models/Juge.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juge extends Model
{
    use HasFactory;

    protected $table = 'juges';
    
    protected $fillable = [
        'nom_complet',
        'grade',
        'specialisation',
        'id_tribunal'
    ];

    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'id_tribunal');
    }

    public function audiences()
    {
        return $this->hasMany(Audience::class, 'id_juge');
    }

    public function jugements()
    {
        return $this->hasMany(Jugement::class, 'id_juge');
    }
}