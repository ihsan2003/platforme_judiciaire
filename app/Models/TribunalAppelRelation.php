<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TribunalAppelRelation extends Model
{
    use HasFactory;

    protected $table = 'tribunal_appel_relations';

    protected $fillable = [
        'tribunal_premier_degre_id',
        'tribunal_appel_id',
        'type_tribunal_id',
    ];

    public function tribunalPremierDegre()
    {
        return $this->belongsTo(Tribunal::class, 'tribunal_premier_degre_id');
    }

    public function tribunalAppel()
    {
        return $this->belongsTo(Tribunal::class, 'tribunal_appel_id');
    }

    public function typeTribunal()
    {
        return $this->belongsTo(TypeTribunal::class, 'type_tribunal_id');
    }
}
