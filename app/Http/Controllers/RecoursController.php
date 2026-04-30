<?php
// app/Http/Controllers/RecoursController.php

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

    // ─────────────────────────────────────────────────────────────────────────
    // STORE — Enregistre un recours et déclenche la transition de degré
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request, Jugement $jugement): RedirectResponse
    {
        $this->authorize('update', $jugement->dossierTribunal->dossier);

        $request->validate([
            'id_type_recours' => ['required', 'exists:type_recours,id'],
            'date_recours'    => ['required', 'date', 'after_or_equal:' . $jugement->date_jugement->toDateString()],
            'motifs'          => ['nullable', 'string', 'max:2000'],
        ]);

        // RG — Impossible si le jugement est déjà définitif
        if ($jugement->est_definitif) {
            return redirect()->back()
                ->with('error', "Ce jugement est définitif : aucun recours n'est possible.");
        }

        // RG — Un seul recours par jugement
        if ($jugement->recours()->exists()) {
            return redirect()->back()
                ->with('error', "Un recours a déjà été déposé sur ce jugement.");
        }

        $typeRecours     = TypeRecours::findOrFail($request->id_type_recours);
        $dossierTribunal = $jugement->dossierTribunal;
        $dossier         = $dossierTribunal->dossier;

        // RG — Vérification du délai légal
        $dateLimite = $jugement->date_jugement->copy()->addDays($typeRecours->delai_legal_jours);
        if (today()->gt($dateLimite)) {
            return redirect()->back()
                ->with('error', "Délai de recours dépassé. La date limite était le {$dateLimite->format('d/m/Y')}.");
        }

        DB::transaction(function () use ($request, $jugement, $dossierTribunal, $dossier, $typeRecours) {

            $recours = Recours::create([
                'id_jugement'         => $jugement->id,
                'id_dossier_tribunal' => $dossierTribunal->id,
                'id_dossier_recours'  => $dossier->id,
                'id_type_recours'     => $typeRecours->id,
                'date_recours'        => $request->date_recours,
                'motifs'              => $request->motifs,
            ]);

            // ── Routage de la transition selon le type de recours ──────────
            // ORDRE CRITIQUE : tester rejet/renvoi AVANT le générique cassation
            $nomType = strtolower($typeRecours->type_recours);

            if ($this->estCassationRejet($nomType)) {
                // Cassation-rejet : l'arrêt d'appel attaqué devient définitif → clôture
                $this->traiterCassationRejet($dossier, $jugement, $dossierTribunal);

            } elseif ($this->estCassationRenvoi($nomType)) {
                // Cassation-renvoi : nouvelle instance d'appel créée
                $this->traiterCassationRenvoi($dossier, $dossierTribunal, $recours);

            } elseif ($this->estUnPourvoi($nomType)) {
                // Pourvoi pur : ouverture d'une instance de cassation
                $this->traiterPourvoi($dossier, $dossierTribunal);

            } elseif ($this->estUnAppel($nomType)) {
                // Appel : ouverture d'une instance d'appel
                $this->traiterAppel($dossier, $dossierTribunal, $recours);

            } else {
                // Type inconnu : on enregistre le recours sans transition de degré
                // Le gestionnaire devra manuellement créer la prochaine instance
            }
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Recours enregistré et statut du dossier mis à jour.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLÔTURE SANS RECOURS — Marque le jugement comme définitif
    // ─────────────────────────────────────────────────────────────────────────
    public function cloturerSansRecours(Jugement $jugement): RedirectResponse
    {
        $this->authorize('update', $jugement->dossierTribunal->dossier);

        if ($jugement->recours()->exists()) {
            return redirect()->back()
                ->with('error', "Ce jugement a déjà fait l'objet d'un recours.");
        }

        if ($jugement->est_definitif) {
            return redirect()->back()
                ->with('error', "Ce jugement est déjà définitif.");
        }

        DB::transaction(function () use ($jugement) {
            $jugement->update(['est_definitif' => true]);

            // Clôturer l'instance (date_fin = aujourd'hui)
            $jugement->dossierTribunal->update(['date_fin' => today()->toDateString()]);

            // Passer le dossier en "Clôturé" seulement si aucun autre recours actif
            $dossier = $jugement->dossierTribunal->dossier;
            if (!$dossier->recours()->whereHas('dossierTribunal', fn($q) => $q->whereNull('date_fin'))->exists()) {
                $this->changerStatutDossier($dossier, 'Clôturé');
            }
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Jugement marqué comme définitif. Dossier clôturé.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TRANSITIONS DE DEGRÉ
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Appel (استئناف) — ferme l'instance 1er degré, ouvre une instance d'appel.
     *
     * Cycle :
     *   DossierTribunal (1er degré, date_fin = today)
     *     → DossierTribunal (استئناف, date_fin = null)
     */
    private function traiterAppel(
        DossierJudiciaire $dossier,
        DossierTribunal   $dtOrigine,
        Recours           $recours
    ): void {
        $degreAppel = $this->trouverDegre('استئناف');

        if ($degreAppel) {
            // Clôturer l'instance d'origine
            $dtOrigine->update(['date_fin' => today()->toDateString()]);

            // Créer la nouvelle instance d'appel
            $idTribunal = $this->trouverTribunalSuivant($dtOrigine, $degreAppel);

            $nouvelleDt = DossierTribunal::create([
                'id_dossier'  => $dossier->id,
                'id_tribunal' => $idTribunal,
                'id_degre'    => $degreAppel->id,
                'date_debut'  => today()->toDateString(),
                'date_fin'    => null,
            ]);

            // Rattacher le recours à la nouvelle instance
            $recours->update(['id_dossier_tribunal' => $nouvelleDt->id]);
        }

        $this->changerStatutDossier($dossier, 'En appel');
    }

    /**
     * Pourvoi en cassation (نقض) — ferme l'instance d'appel, ouvre une instance de cassation.
     *
     * Cycle :
     *   DossierTribunal (استئناف, date_fin = today)
     *     → DossierTribunal (نقض, date_fin = null)
     */
    private function traiterPourvoi(
        DossierJudiciaire $dossier,
        DossierTribunal   $dtOrigine
    ): void {
        $degreCassation = $this->trouverDegre('نقض');

        if ($degreCassation) {
            $dtOrigine->update(['date_fin' => today()->toDateString()]);

            $idTribunal = $this->trouverTribunalSuivant($dtOrigine, $degreCassation);

            DossierTribunal::create([
                'id_dossier'  => $dossier->id,
                'id_tribunal' => $idTribunal,
                'id_degre'    => $degreCassation->id,
                'date_debut'  => today()->toDateString(),
                'date_fin'    => null,
            ]);
        }

        $this->changerStatutDossier($dossier, 'En cassation');
    }

    /**
     * Cassation avec renvoi — ferme l'instance de cassation, rouvre une instance d'appel.
     *
     * Cycle :
     *   DossierTribunal (نقض, date_fin = today)
     *     → DossierTribunal (استئناف, date_fin = null)  ← renvoi
     *
     * Le jugement d'appel attaqué reste non-définitif.
     * La nouvelle instance d'appel repart de zéro (audiences → الحكم → jugement).
     */
    private function traiterCassationRenvoi(
        DossierJudiciaire $dossier,
        DossierTribunal   $dtOrigine,
        Recours           $recours
    ): void {
        $degreAppel = $this->trouverDegre('استئناف');

        if ($degreAppel) {
            $dtOrigine->update(['date_fin' => today()->toDateString()]);

            $idTribunal = $this->trouverTribunalSuivant($dtOrigine, $degreAppel);

            $nouvelleDt = DossierTribunal::create([
                'id_dossier'  => $dossier->id,
                'id_tribunal' => $idTribunal,
                'id_degre'    => $degreAppel->id,
                'date_debut'  => today()->toDateString(),
                'date_fin'    => null,
            ]);

            $recours->update(['id_dossier_tribunal' => $nouvelleDt->id]);
        }

        $this->changerStatutDossier($dossier, 'Réouvert');
    }

    /**
     * Cassation avec rejet — le dernier jugement d'appel devient définitif.
     *
     * Cycle :
     *   Aucune nouvelle instance.
     *   jugement (appel) → est_definitif = true
     *   DossierTribunal (نقض) → date_fin = today
     *   Dossier → Clôturé
     */
    private function traiterCassationRejet(
        DossierJudiciaire $dossier,
        Jugement          $jugement,
        DossierTribunal   $dtCassation
    ): void {
        // Clôturer l'instance de cassation
        $dtCassation->update(['date_fin' => today()->toDateString()]);

        // Trouver le jugement d'appel (instance précédente)
        // qui était à l'origine du pourvoi
        $jugementAppel = $this->trouverJugementAttaque($dossier, $dtCassation);

        if ($jugementAppel) {
            $jugementAppel->update(['est_definitif' => true]);
            // Clôturer aussi l'instance d'appel si ce n'est pas déjà fait
            $jugementAppel->dossierTribunal->update(['date_fin' => today()->toDateString()]);
        }

        // Le jugement de cassation lui-même (s'il existe) est aussi définitif
        $jugement->update(['est_definitif' => true]);

        $this->changerStatutDossier($dossier, 'Clôturé');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Trouve le degré par libellé (recherche partielle insensible à la casse).
     */
    private function trouverDegre(string $libelle): ?DegreeJuridiction
    {
        return DegreeJuridiction::whereRaw('LOWER(degre_juridiction) LIKE ?', [
            '%' . strtolower($libelle) . '%'
        ])->first();
    }

    /**
     * Trouve le tribunal du degré cible dans la même province que l'instance d'origine.
     * Fallback : même tribunal si aucun tribunal du bon degré trouvé.
     */
    private function trouverTribunalSuivant(DossierTribunal $dtOrigine, DegreeJuridiction $degreCible): int
    {
        $provinceId = $dtOrigine->tribunal()->with('province')->first()?->id_province;

        if ($provinceId) {
            $tribunal = Tribunal::where('id_province', $provinceId)
                ->where('id_degre', $degreCible->id)
                ->first();

            if ($tribunal) {
                return $tribunal->id;
            }
        }

        return $dtOrigine->id_tribunal; // fallback
    }

    /**
     * Trouve le jugement attaqué par le pourvoi en cassation.
     * Il s'agit du jugement de l'instance d'appel précédant l'instance de cassation.
     */
    private function trouverJugementAttaque(DossierJudiciaire $dossier, DossierTribunal $dtCassation): ?Jugement
    {
        $degreAppel = $this->trouverDegre('استئناف');
        if (!$degreAppel) {
            return null;
        }

        // L'instance d'appel clôturée la plus récente avant la cassation
        $dtAppel = DossierTribunal::where('id_dossier', $dossier->id)
            ->where('id_degre', $degreAppel->id)
            ->whereNotNull('date_fin')
            ->latest('date_fin')
            ->first();

        return $dtAppel?->jugements()->latest('date_jugement')->first();
    }

    /**
     * Met à jour le statut du dossier en cherchant le libellé en base.
     */
    private function changerStatutDossier(DossierJudiciaire $dossier, string $libelleStatut): void
    {
        $statut = StatutDossier::whereRaw('LOWER(statut_dossier) LIKE ?', [
            '%' . strtolower($libelleStatut) . '%'
        ])->first();

        if ($statut) {
            $dossier->update(['id_statut_dossier' => $statut->id]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLASSIFIEURS DE TYPE DE RECOURS
    // ─────────────────────────────────────────────────────────────────────────

    /** Cassation-rejet : contient "rejet" (à tester AVANT le générique cassation) */
    private function estCassationRejet(string $nom): bool
    {
        return str_contains($nom, 'rejet');
    }

    /** Cassation-renvoi : contient "renvoi" (à tester AVANT le générique cassation) */
    private function estCassationRenvoi(string $nom): bool
    {
        return str_contains($nom, 'renvoi');
    }

    /**
     * Pourvoi pur en cassation : contient "pourvoi" ou "نقض"
     * mais PAS rejet/renvoi (déjà traités avant).
     */
    private function estUnPourvoi(string $nom): bool
    {
        return (str_contains($nom, 'pourvoi') || str_contains($nom, 'نقض'))
            && !str_contains($nom, 'rejet')
            && !str_contains($nom, 'renvoi');
    }

    /**
     * Appel : contient "استئناف" ou "appel"
     * mais PAS "نقض" / cassation.
     */
    private function estUnAppel(string $nom): bool
    {
        return (str_contains($nom, 'استئناف') || str_contains($nom, 'appel'))
            && !str_contains($nom, 'نقض')
            && !str_contains($nom, 'cassation');
    }
}