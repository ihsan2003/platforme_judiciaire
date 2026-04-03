<?php
// app/Models/Juge.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juge extends Model
{
    use HasFactory;

    protected $table = 'juges';

    protected $fillable = [
        'nom_complet',
        'grade',
        'specialisation',
        'id_tribunal',
    ];

    // ─── Relations ────────────────────────────────────────────────────────
    public function tribunal()
    {
        return $this->belongsTo(Tribunal::class, 'id_tribunal');
    }

    public function audiences()
    {
        return $this->hasMany(Audience::class, 'id_juge');
    }

    public function jugements()
    {
        return $this->hasMany(Jugement::class, 'id_juge');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────
    /**
     * Juges d'un tribunal donné.
     */
    public function scopeDuTribunal($query, int $tribunalId)
    {
        return $query->where('id_tribunal', $tribunalId);
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────
    /**
     * Audiences à venir (utile pour afficher la charge de travail).
     */
    public function getAudiencesAVenirCountAttribute(): int
    {
        return $this->audiences()->whereDate('date_audience', '>=', today())->count();
    }

    /**
     * Label affiché (grade + nom).
     */
    public function getLabelAttribute(): string
    {
        return trim(($this->grade ? $this->grade . ' ' : '') . $this->nom_complet);
    }
}
