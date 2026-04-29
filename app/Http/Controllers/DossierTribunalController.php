<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\Tribunal;
use App\Models\DegreeJuridiction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DossierTribunalController extends Controller
{
    /**
     * Assigner un tribunal à un dossier.
     */
    public function store(Request $request, DossierJudiciaire $dossier): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'id_tribunal' => ['required', 'exists:tribunaux,id'],
            'id_degre'    => ['required', 'exists:degre_juridictions,id'],
            'date_debut'  => ['required', 'date'],
            'date_fin'    => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        // RG04 — vérification de l'ordre des degrés
        $erreurDegre = $dossier->peutAjouterDegre((int) $request->id_degre);
        if ($erreurDegre) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->withFragment('tab-tribunaux')
                ->with('error', $erreurDegre);
        }

        DossierTribunal::create([
            'id_dossier'  => $dossier->id,
            'id_tribunal' => $request->id_tribunal,
            'id_degre'    => $request->id_degre,
            'date_debut'  => $request->date_debut,
            'date_fin'    => $request->date_fin,
        ]);

        $tribunal = Tribunal::find($request->id_tribunal);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-tribunaux')
            ->with('success', "Tribunal « {$tribunal->nom_tribunal} » assigné au dossier.");
    }

    /**
     * Mettre à jour les dates ou le degré d'un tribunal assigné.
     */
    public function update(Request $request, DossierJudiciaire $dossier, DossierTribunal $tribunal): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'id_degre'   => ['required', 'exists:degre_juridictions,id'],
            'date_debut' => ['required', 'date'],
            'date_fin'   => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $tribunal->update($request->only(['id_degre', 'date_debut', 'date_fin']));

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-tribunaux')
            ->with('success', 'Tribunal mis à jour.');
    }

    /**
     * Retirer un tribunal du dossier (uniquement si aucune audience ou jugement lié).
     */
    public function destroy(DossierJudiciaire $dossier, DossierTribunal $tribunal): RedirectResponse
    {
        $this->authorize('update', $dossier);

        if ($tribunal->audiences()->exists() || $tribunal->jugements()->exists()) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->withFragment('tab-tribunaux')
                ->with('error', 'Impossible de retirer ce tribunal : il contient des audiences ou des jugements.');
        }

        $tribunal->delete();

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-tribunaux')
            ->with('success', 'Tribunal retiré du dossier.');
    }
}