<?php
// app/Models/TypeJugement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeJugement extends Model
{
    use HasFactory;

    public const PREPARATOIRE = 'حكم تمهيدي';
    public const AU_FOND      = 'حكم ختامي';

    protected $table = 'type_jugements';

    protected $fillable = ['type_jugement'];

    public function jugements()
    {
        return $this->hasMany(Jugement::class, 'id_type_jugement');
    }

    public static function idPreparatoire(): ?int
    {
        return static::where('type_jugement', self::PREPARATOIRE)->value('id');
    }

    public static function idAuFond(): ?int
    {
        return static::where('type_jugement', self::AU_FOND)->value('id');
    }
}
