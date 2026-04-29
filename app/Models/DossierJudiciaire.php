<?php
// app/Models/DossierJudiciaire.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DossierJudiciaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dossier_judiciaires';

    protected $fillable = [
        'numero_dossier_interne',
        'numero_dossier_tribunal',
        'id_type_affaire',
        'id_statut_dossier',
        'date_ouverture',
        'date_cloture',
        'created_by'
    ];

    protected $casts = [
        'date_ouverture' => 'date',
        'date_cloture'   => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    // ─────────────────────────────────────────
    // RELATIONS
    // ─────────────────────────────────────────

    public function typeAffaire()
    {
        return $this->belongsTo(TypeAffaire::class, 'id_type_affaire');
    }

    /**
     * Relation principale — utilisée partout dans les controllers.
     */
    public function statut()
    {
        return $this->belongsTo(StatutDossier::class, 'id_statut_dossier');
    }

    /**
     * Alias pour la compatibilité avec les vues et la Policy
     * qui appellent $dossier->statutDossier->statut_dossier.
     *
     * Les deux noms pointent sur la même table / clé étrangère.
     */
    public function statutDossier()
    {
        return $this->belongsTo(StatutDossier::class, 'id_statut_dossier');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parties()
    {
        return $this->belongsToMany(Partie::class, 'dossier_parties', 'id_dossier', 'id_partie')
                    ->withPivot(['id_type_partie', 'id_avocat', 'est_institution', 'date_entree'])
                    ->withTimestamps();
    }

    public function tribunaux()
    {
        return $this->belongsToMany(Tribunal::class, 'dossier_tribunaux', 'id_dossier', 'id_tribunal')
                    ->withPivot(['id_degre', 'date_debut', 'date_fin'])
                    ->withTimestamps();
    }

    public function dossierTribunaux()
    {
        return $this->hasMany(DossierTribunal::class, 'id_dossier');
    }

    public function audiences()
    {
        return $this->hasManyThrough(Audience::class, DossierTribunal::class, 'id_dossier', 'id_dossier_tribunal');
    }

    public function jugements()
    {
        return $this->hasManyThrough(Jugement::class, DossierTribunal::class, 'id_dossier', 'id_dossier_tribunal');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'id_dossier');
    }

    public function recours()
    {
        return $this->hasMany(Recours::class, 'id_dossier_recours');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_dossier');
    }

    public function dossierParties()
    {
        return $this->hasMany(\App\Models\DossierPartie::class, 'id_dossier');
    }

    // ─────────────────────────────────────────
    // RÈGLES MÉTIER
    // ─────────────────────────────────────────

    /**
 * RG04 — Le degré 3 nécessite d'avoir déjà eu les degrés 1 et 2.
 * Retourne null si OK, sinon un message d'erreur.
 */
    public function peutAjouterDegre(int $degreId): ?string
    {
        $degreVise = \App\Models\DegreeJuridiction::find($degreId);
        if (! $degreVise) {
            return 'Degré introuvable.';
        }

        // On récupère les ordres des degrés déjà présents dans ce dossier
        $degresExistants = $this->dossierTribunaux()
            ->with('degre')
            ->get()
            ->pluck('degre')
            ->filter()
            ->pluck('ordre') // champ "ordre" : 1=premier degré, 2=appel, 3=cassation
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $ordreVise = $degreVise->ordre;

        // Vérification séquentielle
        for ($i = 1; $i < $ordreVise; $i++) {
            if (! in_array($i, $degresExistants)) {
                $libelle = match($i) {
                    1 => 'premier degré',
                    2 => 'appel (2ème degré)',
                    default => "degré {$i}",
                };
                return "Impossible d'ajouter ce degré : le {$libelle} est requis au préalable.";
            }
        }

        return null; // OK
    }
    
    public function typesPartiesManquants(): array
    {
        $typesRequis = ['المدعي', 'المدعى عليه'];

        $typesPresents = $this->dossierParties()
            ->with('typePartie')
            ->get()
            ->pluck('typePartie.type_partie')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return array_values(array_diff($typesRequis, $typesPresents));
    }

    public function peutAvoirAudience(): bool
    {
        return $this->typesPartiesManquants() === [];
    }

    // ─────────────────────────────────────────
    // HOOKS
    // ─────────────────────────────────────────

    protected static function booted()
    {
        static::creating(function ($dossier) {
            if (!$dossier->id_statut_dossier) {
                // Cherche d'abord le statut arabe 'جاري', puis 'Actif' en fallback
                $statut = StatutDossier::where('statut_dossier', 'جاري')->first()
                       ?? StatutDossier::whereRaw("LOWER(statut_dossier) LIKE '%actif%'")->first()
                       ?? StatutDossier::first();

                $dossier->id_statut_dossier = $statut?->id;
            }
        });
    }

    // ─────────────────────────────────────────
    // ACCESSEURS
    // ─────────────────────────────────────────

    public function getEstActifAttribute(): bool
    {
        $statut = $this->statut?->statut_dossier ?? '';
        return !str_contains(strtolower($statut), 'clôturé')
            && !str_contains(strtolower($statut), 'cloturé');
    }

    public function getDureeTraitementAttribute(): ?int
    {
        if ($this->date_cloture) {
            return $this->date_cloture->diffInDays($this->date_ouverture);
        }
        return null;
    }

    // ─────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->whereHas('statut', function ($q) {
            $q->whereRaw("LOWER(statut_dossier) NOT LIKE '%clôturé%'")
              ->whereRaw("LOWER(statut_dossier) NOT LIKE '%cloture%'");
        });
    }

    public function scopeParType($query, $typeId)
    {
        return $query->where('id_type_affaire', $typeId);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_ouverture', [$debut, $fin]);
    }
}