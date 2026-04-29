<?php
// app/Models/Reclamation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reclamation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reclamations';
    
    protected $fillable = [
        'id_reclamant',
        'objet',
        'date_reception',
        'id_statut_reclamation',
        'details'
    ];

    protected $casts = [
        'date_reception' => 'date'
    ];

    public function reclamant()
    {
        return $this->belongsTo(Reclamant::class, 'id_reclamant');
    }

    public function statut()
    {
        return $this->belongsTo(StatutReclamation::class, 'id_statut_reclamation');
    }

    public function actions()
    {
        return $this->hasMany(ActionReclamation::class, 'id_reclamation')->orderBy('created_at', 'desc');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'id_reclamation');
    }

//    public function notifications()
//    {
   //     return $this->hasMany(Notification::class, 'id_reclamation');
    //}

    public function getDerniereActionAttribute()
    {
        return $this->actions()->first();
    }

    public function getDureeTraitementAttribute(): ?int
    {
        $premiereAction = $this->actions()->oldest()->first();
        $derniereAction = $this->actions()->latest()->first();
        
        if ($premiereAction && $derniereAction) {
            return $premiereAction->created_at->diffInDays($derniereAction->created_at);
        }
        
        return $this->created_at->diffInDays(now());
    }

    public function scopeEnAttente($query)
    {
        return $query->whereHas('statut', function($q) {
            $q->whereIn('statut_reclamation', ['Reçue', 'En cours']);
        });
    }
}