<?php

namespace App\Http\Controllers;

use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\DegreeJuridiction;
use App\Models\Jugement;
use App\Models\Recours;
use App\Models\StatutDossier;
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

    public function store(Request $request, Jugement $jugement): RedirectResponse
    {
        $this->authorize('update', $jugement->dossierTribunal->dossier);

        $request->validate([
            'id_type_recours' => ['required', 'exists:type_recours,id'],
            'date_recours'    => ['required', 'date', 'after_or_equal:' . $jugement->date_jugement->toDateString()],
            'motifs'          => ['nullable', 'string', 'max:2000'],
        ]);

        $dossierTribunal = $jugement->dossierTribunal;
        $dossier         = $dossierTribunal->dossier;
        $typeRecours     = TypeRecours::findOrFail($request->id_type_recours);

        // ✅ Correct — ne mute pas l'objet original
        $dateLimite = $jugement->date_jugement->copy()->addDays($typeRecours->delai_legal_jours);
        if (now()->gt($dateLimite)) {
            return redirect()->back()
                ->with('error', "Délai de recours dépassé. La date limite était le {$dateLimite->format('d/m/Y')}.");
        }

        if ($jugement->est_definitif) {
            return redirect()->back()
                ->with('error', "Ce jugement est définitif : aucun recours n'est possible.");
        }

        DB::transaction(function () use ($request, $jugement, $dossierTribunal, $dossier, $typeRecours) {

            $recours = Recours::create([
                'id_jugement'         => $jugement->id,
                'id_dossier_tribunal' => $dossierTribunal->id,
                'id_type_recours'     => $typeRecours->id,
                'date_recours'        => $request->date_recours,
                'motifs'              => $request->motifs,
            ]);

            // ⚠️ ORDRE CRITIQUE : rejet et renvoi AVANT pourvoi/cassation générique
            // car "cassation rejet" contient "cassation" → serait mal routé sinon
            $nomType = strtolower($typeRecours->type_recours);

            if ($this->estCassationRejet($nomType)) {
                $this->traiterCassationRejet($dossier, $jugement);

            } elseif ($this->estCassationRenvoi($nomType)) {
                $this->traiterCassationRenvoi($dossier, $dossierTribunal, $recours);

            } elseif ($this->estUnPourvoi($nomType)) {
                $this->traiterPourvoi($dossier, $dossierTribunal);

            } elseif ($this->estUnAppel($nomType)) {
                $this->traiterAppel($dossier, $dossierTribunal, $recours);
            }
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Recours enregistré et statut du dossier mis à jour.');
    }

    public function cloturerSansRecours(Jugement $jugement): RedirectResponse
    {
        $this->authorize('update', $jugement->dossierTribunal->dossier);

        if ($jugement->recours()->exists()) {
            return redirect()->back()
                ->with('error', "Ce jugement a déjà fait l'objet d'un recours.");
        }

        DB::transaction(function () use ($jugement) {
            $jugement->update(['est_definitif' => true]);
            $this->changerStatutDossier($jugement->dossierTribunal->dossier, 'Clôturé');
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Jugement marqué comme définitif. Dossier clôturé.');
    }

    // ── Transitions ────────────────────────────────────────────────────────

    private function traiterAppel(DossierJudiciaire $dossier, DossierTribunal $dtOrigine, Recours $recours): void
    {
        $degreAppel = $this->trouverDegre('Appel');
        if ($degreAppel) {
            $dtOrigine->update(['date_fin' => now()]);
            $nouvelle = DossierTribunal::create([
                'id_dossier'  => $dossier->id,
                'id_tribunal' => $dtOrigine->id_tribunal,
                'id_degre'    => $degreAppel->id,
                'date_debut'  => now(),
                'date_fin'    => null,
            ]);
            $recours->update(['id_dossier_tribunal' => $nouvelle->id]);
        }
        $this->changerStatutDossier($dossier, 'En appel');
    }

    private function traiterPourvoi(DossierJudiciaire $dossier, DossierTribunal $dtOrigine): void
    {
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

    private function traiterCassationRejet(DossierJudiciaire $dossier, Jugement $jugement): void
    {
        $jugement->update(['est_definitif' => true]);
        $this->changerStatutDossier($dossier, 'Clôturé');
    }

    private function traiterCassationRenvoi(DossierJudiciaire $dossier, DossierTribunal $dtOrigine, Recours $recours): void
    {
        $degreAppel = $this->trouverDegre('Appel');
        if ($degreAppel) {
            $dtOrigine->update(['date_fin' => now()]);
            $nouvelle = DossierTribunal::create([
                'id_dossier'  => $dossier->id,
                'id_tribunal' => $dtOrigine->id_tribunal,
                'id_degre'    => $degreAppel->id,
                'date_debut'  => now(),
                'date_fin'    => null,
            ]);
            $recours->update(['id_dossier_tribunal' => $nouvelle->id]);
        }
        $this->changerStatutDossier($dossier, 'Réouvert');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function trouverDegre(string $libelle): ?DegreeJuridiction
    {
        return DegreeJuridiction::whereRaw('LOWER(degre_juridiction) LIKE ?', [
            '%' . strtolower($libelle) . '%'
        ])->first();
    }

    private function changerStatutDossier(DossierJudiciaire $dossier, string $libelleStatut): void
    {
        $statut = StatutDossier::whereRaw('LOWER(statut_dossier) LIKE ?', [
            '%' . strtolower($libelleStatut) . '%'
        ])->first();

        if ($statut) {
            $dossier->update(['id_statut_dossier' => $statut->id]);
        }
    }

    // "rejet" doit être testé AVANT "cassation" générique
    private function estCassationRejet(string $nom): bool  { return str_contains($nom, 'rejet'); }
    private function estCassationRenvoi(string $nom): bool { return str_contains($nom, 'renvoi'); }

    // Pourvoi pur = contient "pourvoi" ou "cassation" MAIS PAS rejet/renvoi
    private function estUnPourvoi(string $nom): bool
    {
        return (str_contains($nom, 'pourvoi') || str_contains($nom, 'cassation'))
            && !str_contains($nom, 'rejet')
            && !str_contains($nom, 'renvoi');
    }

    // Appel = contient "appel" mais pas "cassation"
    private function estUnAppel(string $nom): bool
    {
        return str_contains($nom, 'appel') && !str_contains($nom, 'cassation');
    }
}