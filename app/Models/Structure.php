<?php
// app/Models/Structure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Structure extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'structures';
    
    protected $fillable = [
        'nom',
        'id_type_structure',
        'id_parent'
    ];

    public function typeStructure()
    {
        return $this->belongsTo(TypeStructure::class, 'id_type_structure');
    }

    public function parent()
    {
        return $this->belongsTo(Structure::class, 'id_parent');
    }

    public function enfants()
    {
        return $this->hasMany(Structure::class, 'id_parent');
    }

    public function actionReclamations()
    {
        return $this->hasMany(ActionReclamation::class, 'id_structure');
    }

    public function getHierarchieAttribute(): string
    {
        $noms = [];
        $structure = $this;
        
        while ($structure) {
            $noms[] = $structure->nom;
            $structure = $structure->parent;
        }
        
        return implode(' > ', array_reverse($noms));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nom',
                'id_type_structure',
                'id_parent',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('structures');

    }
}