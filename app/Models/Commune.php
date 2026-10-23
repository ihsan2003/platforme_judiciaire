<?php
// app/Models/Commune.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Commune extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'communes';

    protected $fillable = ['commune', 'id_province'];

    public function province()
    {
        return $this->belongsTo(Province::class, 'id_province');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'commune',
                'id_province',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('communes');
    }
}
