<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DossierJudiciaire;
use App\Models\DossierPartie;
use App\Models\Partie;
use App\Rules\Telephone;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DossierPartieController extends Controller
{
    /**
 * Recherche de parties existantes par identifiant ou nom (AJAX).
 */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $parties = Partie::with('avocat')
            ->where('identifiant_unique', 'like', "%{$q}%")
            ->orWhere('nom_partie', 'like', "%{$q}%")
            ->orderBy('nom_partie')
            ->limit(10)
            ->get(['id', 'identifiant_unique', 'nom_partie', 'type_personne',
                'telephone', 'email', 'adresse', 'id_avocat']);

        // Ajouter le nom de l'avocat pour l'affichage dans le dropdown
        $parties->transform(fn($p) => array_merge($p->toArray(), [
            'avocat_nom' => $p->avocat?->nom_avocat,
        ]));

        return response()->json($parties);
    }
    /**
     * Ajouter une partie existante ou nouvelle au dossier.
     */
    public function store(Request $request, DossierJudiciaire $dossier): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'identifiant_unique' => ['required', 'string', 'max:255'],
            'nom_partie'         => ['required_without:partie_id', 'nullable', 'string', 'max:255'],
            'type_personne'      => ['required_without:partie_id', 'nullable', 'in:Physique,Morale'],
            'telephone'          => ['nullable', new \App\Rules\TelephoneMaroc],
            'email'              => ['nullable', 'email', 'max:255'],
            'adresse'            => ['nullable', 'string'],
            'id_avocat'          => ['nullable', 'exists:avocats,id'], // pour nouvelle partie
            'id_type_partie'     => ['required', 'exists:type_parties,id'],
            'date_entree'        => ['required', 'date'],
        ]);

        if ($request->filled('partie_id')) {
            $partie = Partie::findOrFail($request->partie_id);

            // Mettre à jour l'avocat si on en fournit un différent
            if ($request->filled('id_avocat') && $partie->id_avocat != $request->id_avocat) {
                $partie->update(['id_avocat' => $request->id_avocat]);
            }
        } else {
            $partie = Partie::firstOrCreate(
                ['identifiant_unique' => $request->identifiant_unique],
                [
                    'nom_partie'    => $request->nom_partie,
                    'type_personne' => $request->type_personne ?? 'Physique',
                    'telephone'     => $request->telephone,
                    'email'         => $request->email,
                    'adresse'       => $request->adresse,
                    'id_avocat'     => $request->id_avocat, // ← sur la partie directement
                ]
            );
        }

        $existe = DossierPartie::where('id_dossier', $dossier->id)
            ->where('id_partie', $partie->id)
            ->where('id_type_partie', $request->id_type_partie)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->withFragment('tab-parties')
                ->with('error', 'Cette partie est déjà enregistrée dans ce dossier avec ce rôle.');
        }

        DossierPartie::create([
            'id_dossier'     => $dossier->id,
            'id_partie'      => $partie->id,
            'id_type_partie' => $request->id_type_partie,
            'date_entree'    => $request->date_entree,
            // plus d'id_avocat ici — il est sur la partie
        ]);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-parties')
            ->with('success', "Partie « {$partie->nom_partie} » ajoutée au dossier.");
    }

    public function update(Request $request, DossierJudiciaire $dossier, DossierPartie $partie): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'id_type_partie' => ['required', 'exists:type_parties,id'],
            'id_avocat'      => ['nullable', 'exists:avocats,id'],
            'date_entree'    => ['required', 'date'],
            // pas de est_entraide ici
        ]);

        $partie->update($request->only(['id_type_partie', 'id_avocat', 'date_entree']));

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-parties')
            ->with('success', 'Informations de la partie mises à jour.');
    }

    /**
     * Retirer une partie du dossier (supprime uniquement la liaison).
     */
    public function destroy(DossierJudiciaire $dossier, DossierPartie $partie): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $nomPartie = $partie->partie->nom_partie;
        $partie->delete();

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-parties')
            ->with('success', "Partie « {$nomPartie} » retirée du dossier.");
    }
}