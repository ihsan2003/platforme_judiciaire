<?php
// app/Models/Region.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Region extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'regions';
    
    protected $fillable = ['region'];

    public function provinces()
    {
        return $this->hasMany(Province::class, 'id_region');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'region',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('regions');
    }
}