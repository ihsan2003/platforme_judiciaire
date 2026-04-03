<?php
// app/Models/ActionReclamation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionReclamation extends Model
{
    use HasFactory;

    protected $table = 'action_reclamations';
    
    protected $fillable = [
        'id_reclamation',
        'id_type_action',
        'statut_action',
        'date_action',
        'id_structure',
        'commentaire',
        'created_by'
    ];

    protected $casts = [
        'date_action' => 'date'
    ];

    public function reclamation()
    {
        return $this->belongsTo(Reclamation::class, 'id_reclamation');
    }

    public function typeAction()
    {
        return $this->belongsTo(TypeAction::class, 'id_type_action');
    }

    public function structure()
    {
        return $this->belongsTo(Structure::class, 'id_structure');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'id_action');
    }
}