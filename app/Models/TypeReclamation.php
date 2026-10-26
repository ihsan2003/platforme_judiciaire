<?php
// app/Models/TypeReclamation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeReclamation extends Model
{
    use HasFactory;

    /** Libellé du type traité comme un dossier (cf. migration create_type_reclamations_table). */
    public const JUDICIAIRE = 'شكايات مرتبطة بالمنازعات القضائية';

    protected $table = 'type_reclamations';

    protected $fillable = ['type_reclamation'];

    /** Id du type « شكايات مرتبطة بالمنازعات القضائية » (null s'il n'existe pas). */
    public static function idJudiciaire(): ?int
    {
        return static::where('type_reclamation', self::JUDICIAIRE)->value('id');
    }

    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'id_type_reclamation');
    }
}