<?php
// app/Models/Province.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Province extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'province',
                'id_region',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('provinces');

    }

}