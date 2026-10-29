<?php
// app/Models/DegreJuridiction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeJuridiction extends Model
{
    use HasFactory;

    protected $table = 'degre_juridictions';
    
    protected $fillable = ['degre_juridiction', 'ordre'];

    /**
     * Degré « première instance » : même critère de libellé que le reste de
     * l'application (الدرجة الأولى), repli sur ordre = 1.
     */
    public static function premierDegre(): ?self
    {
        return self::where('degre_juridiction', 'LIKE', '%الأولى%')->first()
            ?? self::where('ordre', 1)->orderBy('id')->first();
    }

    public function dossierTribunaux()
    {
        return $this->hasMany(DossierTribunal::class, 'id_degre');
    }

    public function tribunaux()
    {
        return $this->hasMany(Tribunal::class, 'id_degre');
    }
}