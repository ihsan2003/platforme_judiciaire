<?php
// app/Models/TypeAction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeAction extends Model
{
    use HasFactory;

    protected $table = 'type_actions';
    
    protected $fillable = ['type_action'];

    public function actionReclamations()
    {
        return $this->hasMany(ActionReclamation::class, 'id_type_action');
    }
}