<?php
// app/Models/Tribunal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tribunal extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'tribunaux';
    
    protected $fillable = [
        'nom_tribunal',
        'id_type_tribunal',
        'id_province',
        'id_degre',
        'id_parent',
    ];

    public function typeTribunal()
    {
        return $this->belongsTo(TypeTribunal::class, 'id_type_tribunal');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'id_province');
    }

    public function degre()
    {
        return $this->belongsTo(DegreeJuridiction::class, 'id_degre');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'id_parent');
    }

    public function enfants()
    {
        return $this->hasMany(self::class, 'id_parent');
    }

    public function juges()
    {
        return $this->hasMany(Juge::class, 'id_tribunal');
    }

    public function dossierTribunaux()
    {
        return $this->hasMany(DossierTribunal::class, 'id_tribunal');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nom_tribunal',
                'id_type_tribunal',
                'id_province',
                'id_degre',
                'id_parent',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('tribunaux');

    }

}