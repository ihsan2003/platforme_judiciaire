<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'id_utilisateur',
        'type_notification',
        'niveau',
        'message',
        'details',
        'id_dossier',
        'id_audience',
        'id_jugement',
        'id_reclamation',
        'id_execution',
        'url_action',
        'est_lue',
        'date_lecture',
        'cle_dedup',
    ];

    protected $casts = [
        'est_lue'       => 'boolean',
        'date_lecture'  => 'datetime',
        'created_at'    => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }

    public function dossier()
    {
        return $this->belongsTo(DossierJudiciaire::class, 'id_dossier');
    }

    public function audience()
    {
        return $this->belongsTo(Audience::class, 'id_audience');
    }

    public function jugement()
    {
        return $this->belongsTo(Jugement::class, 'id_jugement');
    }

    public function reclamation()
    {
        return $this->belongsTo(Reclamation::class, 'id_reclamation');
    }

    public function execution()
    {
        return $this->belongsTo(Execution::class, 'id_execution');
    }

    // ─── Actions ──────────────────────────────────────────────────────────

    public function marquerCommeLue(): void
    {
        $this->update([
            'est_lue'      => true,
            'date_lecture' => now(),
        ]);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeNonLues($query)
    {
        return $query->where('est_lue', false);
    }

    public function scopePourUtilisateur($query, int $userId)
    {
        return $query->where('id_utilisateur', $userId);
    }

    public function scopeParNiveau($query, string $niveau)
    {
        return $query->where('niveau', $niveau);
    }

    // ─── Accesseurs ───────────────────────────────────────────────────────

    /**
     * Couleur Bootstrap selon le niveau.
     */
    public function getCouleurAttribute(): string
    {
        return match($this->niveau) {
            'danger'  => 'danger',
            'warning' => 'warning',
            default   => 'info',
        };
    }

    /**
     * Icône Bootstrap Icons selon le type.
     */
    public function getIconeAttribute(): string
    {
        return match($this->type_notification) {
            'audience_proche'        => 'bi-calendar-event',
            'delai_recours'          => 'bi-clock-history',
            'jugement_non_definitif' => 'bi-hammer',
            'reclamation_en_attente' => 'bi-envelope-exclamation',
            'execution_en_retard'    => 'bi-exclamation-triangle',
            default                  => 'bi-bell',
        };
    }

    /**
     * Libellé de la catégorie pour l'affichage.
     */
    public function getCategorieAttribute(): string
    {
        return match($this->type_notification) {
            'audience_proche'        => 'Audience',
            'delai_recours'          => 'Délai de recours',
            'jugement_non_definitif' => 'Jugement',
            'reclamation_en_attente' => 'Réclamation',
            'execution_en_retard'    => 'Exécution',
            default                  => 'Système',
        };
    }
}