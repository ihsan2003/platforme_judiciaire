<?php
// app/Models/TypeDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDocument extends Model
{
    use HasFactory;

    protected $table = 'type_documents';
    
    protected $fillable = ['type_document'];

    public function documents()
    {
        return $this->hasMany(Document::class, 'id_type_document');
    }
}