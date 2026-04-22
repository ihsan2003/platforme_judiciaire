<?php

namespace App\Http\Controllers;

use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\DegreeJuridiction;
use App\Models\Jugement;
use App\Models\Recours;
use App\Models\StatutDossier;
use App\Models\Tribunal;
use App\Models\TypeRecours;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecoursController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================
    // STORE — Déposer un recours et appliquer les règles métier
    //
    // Règles métier appliquées :
    // ┌─────────────────────┬───────────────┬──────────────┐
    // │ Situation           │ Nouveau degré │ Statut       │
    // ├─────────────────────┼───────────────┼──────────────┤
    // │ Jugement sans appel │ même degré    │ Clôturé      │
    // │ Appel déposé        │ degré suivant │ En appel     │
    // │ Pourvoi cassation   │ cassation     │ En cassation │
    // │ Cassation rejet     │ aucun         │ Clôturé      │
    // │ Cassation renvoi    │ appel         │ Réouvert     │
    // └─────────────────────┴───────────────┴──────────────┘
    // =========================================================
    public function store(Request $request, Jugement $jugement): RedirectResponse
    {
        $request->validate([
            'id_type_recours' => ['required', 'exists:type_recours,id'],
            'date_recours'    => ['required', 'date', 'after_or_equal:' . $jugement->date_jugement->toDateString()],
            'motifs'          => ['nullable', 'string', 'max:2000'],
        ]);

        $dossierTribunal = $jugement->dossierTribunal;
        $dossier         = $dossierTribunal->dossier;
        $typeRecours     = TypeRecours::findOrFail($request->id_type_recours);

        // RG01 — Vérifier que le délai légal n'est pas dépassé
        $dateLimite = $jugement->date_jugement->addDays($typeRecours->delai_legal_jours);
        if (now()->gt($dateLimite)) {
            return redirect()->back()
                ->with('error', "Délai de recours dépassé. La date limite était le {$dateLimite->format('d/m/Y')}.");
        }

        // RG02 — Un jugement définitif ne peut plus faire l'objet d'un recours
        if ($jugement->est_definitif) {
            return redirect()->back()
                ->with('error', 'Ce jugement est définitif : aucun recours n\'est possible.');
        }

        DB::transaction(function () use ($request, $jugement, $dossierTribunal, $dossier, $typeRecours) {

            // 1. Enregistrer le recours
            $recours = Recours::create([
                'id_jugement'         => $jugement->id,
                'id_dossier_tribunal' => $dossierTribunal->id,
                'id_type_recours'     => $typeRecours->id,
                'date_recours'        => $request->date_recours,
                'motifs'              => $request->motifs,
            ]);

            // 2. Appliquer les transitions selon le type de recours
            $nomType = strtolower($typeRecours->type_recours);

            if ($this->estUnAppel($nomType)) {
                // ── Appel déposé → créer une nouvelle instance d'appel ──
                $this->traiterAppel($dossier, $dossierTribunal, $recours);

            } elseif ($this->estUnPourvoi($nomType)) {
                // ── Pourvoi en cassation ──
                $this->traiterPourvoi($dossier, $dossierTribunal);

            } elseif ($this->estCassationRejet($nomType)) {
                // ── Cassation avec rejet → jugement devient définitif, dossier clôturé ──
                $this->traiterCassationRejet($dossier, $jugement);

            } elseif ($this->estCassationRenvoi($nomType)) {
                // ── Cassation avec renvoi → retour en appel ──
                $this->traiterCassationRenvoi($dossier, $dossierTribunal, $recours);
            }
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Recours enregistré et statut du dossier mis à jour.');
    }

    // =========================================================
    // ACTION : Clôturer manuellement (aucun recours déposé)
    //
    // Si aucun recours n'est déposé dans le délai légal,
    // le jugement devient définitif et le dossier est clôturé.
    // =========================================================
    public function cloturerSansRecours(Jugement $jugement): RedirectResponse
    {
        $this->authorize('update', $jugement->dossierTribunal->dossier);

        if ($jugement->recours()->exists()) {
            return redirect()->back()
                ->with('error', 'Ce jugement a déjà fait l\'objet d\'un recours.');
        }

        DB::transaction(function () use ($jugement) {
            // Marquer le jugement comme définitif
            $jugement->update(['est_definitif' => true]);

            // Clôturer le dossier
            $this->changerStatutDossier(
                $jugement->dossierTribunal->dossier,
                'Clôturé'
            );
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Jugement marqué comme définitif. Dossier clôturé.');
    }

    // =========================================================
    // TRANSITIONS MÉTIER PRIVÉES
    // =========================================================

    /**
     * Appel déposé dans le délai légal.
     *
     * → Crée une nouvelle instance DossierTribunal au degré "Appel"
     * → Met le statut du dossier à "En appel"
     * → Le jugement de première instance n'est PAS encore définitif
     */
    private function traiterAppel(
        DossierJudiciaire $dossier,
        DossierTribunal   $dtOrigine,
        Recours           $recours
    ): void {
        $degreAppel = $this->trouverDegre('Appel');

        if ($degreAppel) {
            // Clôturer l'instance précédente
            $dtOrigine->update(['date_fin' => now()]);

            // Créer la nouvelle instance d'appel sur le même tribunal
            $nouvelleInstance = DossierTribunal::create([
                'id_dossier'  => $dossier->id,
                'id_tribunal' => $dtOrigine->id_tribunal,
                'id_degre'    => $degreAppel->id,
                'date_debut'  => now(),
                'date_fin'    => null,
            ]);

            // Lier le recours à la nouvelle instance
            $recours->update(['id_dossier_tribunal' => $nouvelleInstance->id]);
        }

        $this->changerStatutDossier($dossier, 'En appel');
    }

    /**
     * Pourvoi en cassation.
     *
     * → Crée une instance au degré "Cassation"
     * → Statut du dossier : "En cassation"
     */
    private function traiterPourvoi(
        DossierJudiciaire $dossier,
        DossierTribunal   $dtOrigine
    ): void {
        $degreCassation = $this->trouverDegre('Cassation');

        if ($degreCassation) {
            $dtOrigine->update(['date_fin' => now()]);

            DossierTribunal::create([
                'id_dossier'  => $dossier->id,
                'id_tribunal' => $dtOrigine->id_tribunal,
                'id_degre'    => $degreCassation->id,
                'date_debut'  => now(),
                'date_fin'    => null,
            ]);
        }

        $this->changerStatutDossier($dossier, 'En cassation');
    }

    /**
     * Cassation avec rejet du pourvoi.
     *
     * → Le jugement attaqué devient définitif
     * → Dossier clôturé
     */
    private function traiterCassationRejet(
        DossierJudiciaire $dossier,
        Jugement          $jugement
    ): void {
        $jugement->update(['est_definitif' => true]);
        $this->changerStatutDossier($dossier, 'Clôturé');
    }

    /**
     * Cassation avec renvoi.
     *
     * → Nouvelle instance au degré "Appel" (renvoi devant une autre cour)
     * → Statut du dossier : "Réouvert"
     */
    private function traiterCassationRenvoi(
        DossierJudiciaire $dossier,
        DossierTribunal   $dtOrigine,
        Recours           $recours
    ): void {
        $degreAppel = $this->trouverDegre('Appel');

        if ($degreAppel) {
            $dtOrigine->update(['date_fin' => now()]);

            $nouvelleInstance = DossierTribunal::create([
                'id_dossier'  => $dossier->id,
                'id_tribunal' => $dtOrigine->id_tribunal,
                'id_degre'    => $degreAppel->id,
                'date_debut'  => now(),
                'date_fin'    => null,
            ]);

            $recours->update(['id_dossier_tribunal' => $nouvelleInstance->id]);
        }

        $this->changerStatutDossier($dossier, 'Réouvert');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /** Cherche un DegreeJuridiction par son libellé (insensible à la casse). */
    private function trouverDegre(string $libelle): ?DegreeJuridiction
    {
        return DegreeJuridiction::whereRaw('LOWER(degre_juridiction) LIKE ?', [
            '%' . strtolower($libelle) . '%'
        ])->first();
    }

    /** Met à jour le statut_dossier via la table statut_dossiers. */
    private function changerStatutDossier(DossierJudiciaire $dossier, string $libelleStatut): void
    {
        $statut = StatutDossier::whereRaw('LOWER(statut_dossier) LIKE ?', [
            '%' . strtolower($libelleStatut) . '%'
        ])->first();

        if ($statut) {
            $dossier->update(['id_statut_dossier' => $statut->id]);
        }
    }

    private function estUnAppel(string $nom): bool
    {
        return str_contains($nom, 'appel') && !str_contains($nom, 'cassation');
    }

    private function estUnPourvoi(string $nom): bool
    {
        return str_contains($nom, 'pourvoi') || str_contains($nom, 'cassation');
    }

    private function estCassationRejet(string $nom): bool
    {
        return str_contains($nom, 'rejet');
    }

    private function estCassationRenvoi(string $nom): bool
    {
        return str_contains($nom, 'renvoi');
    }
}