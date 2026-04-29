<?php
// app/Models/Document.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';
    
    protected $fillable = [
        'id_dossier',
        'id_reclamation',
        'id_type_document',
        'id_partie',
        'titre_document',
        'date_depot',
        'fichier_path'
    ];

    protected $casts = [
        'date_depot' => 'date'
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierJudiciaire::class, 'id_dossier');
    }

    public function reclamation()
    {
        return $this->belongsTo(Reclamation::class, 'id_reclamation');
    }


    public function typeDocument()
    {
        return $this->belongsTo(TypeDocument::class, 'id_type_document');
    }

    public function partie()
    {
        return $this->belongsTo(Partie::class, 'id_partie');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->fichier_path);
    }

    public function getExtensionAttribute(): string
    {
        return pathinfo($this->fichier_path, PATHINFO_EXTENSION);
    }

    public function getTailleAttribute(): ?int
    {
        if ($this->fichier_path && Storage::exists($this->fichier_path)) {
            return Storage::size($this->fichier_path);
        }
        return null;
    }
}