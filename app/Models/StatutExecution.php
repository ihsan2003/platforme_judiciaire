<?php
// app/Models/StatutExecution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutExecution extends Model
{
    use HasFactory;

    protected $table = 'statut_executions';
    
    protected $fillable = ['statut_execution'];

    public function executions()
    {
        return $this->hasMany(Execution::class, 'statut_execution');
    }
}