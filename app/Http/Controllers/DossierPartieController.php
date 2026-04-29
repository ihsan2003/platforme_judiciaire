<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DossierJudiciaire;
use App\Models\DossierPartie;
use App\Models\Partie;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DossierPartieController extends Controller
{
    /**
     * Recherche de parties existantes par identifiant ou nom (AJAX).
     * Retourne aussi l'avocat lié à la partie pour affichage informatif.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $parties = Partie::with('avocat')
            ->where(function ($query) use ($q) {
                $query->where('identifiant_unique', 'like', "%{$q}%")
                      ->orWhere('nom_partie', 'like', "%{$q}%");
            })
            ->orderBy('nom_partie')
            ->limit(10)
            ->get(['id', 'identifiant_unique', 'nom_partie', 'type_personne',
                'telephone', 'email', 'adresse', 'id_avocat']);

        // Inclure le nom de l'avocat pour affichage (lecture seule)
        $parties->transform(fn($p) => array_merge($p->toArray(), [
            'avocat_nom' => $p->avocat?->nom_avocat,
        ]));

        return response()->json($parties);
    }

    /**
     * Ajouter une partie existante ou nouvelle au dossier.
     *
     * RG : l'avocat est lié à la partie elle-même, pas au dossier.
     * On ne demande l'avocat que lors de la création d'une nouvelle partie.
     * Pour une partie existante, l'avocat peut être mis à jour sur la partie
     * uniquement si l'utilisateur le change explicitement.
     */
    public function store(Request $request, DossierJudiciaire $dossier): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'identifiant_unique' => ['required', 'string', 'max:255'],
            'nom_partie'         => ['required_without:partie_id', 'nullable', 'string', 'max:255'],
            'type_personne'      => ['required_without:partie_id', 'nullable', 'in:Physique,Morale'],
            'telephone'          => ['nullable', 'regex:/^(\+212|00212|0)(5|6|7)[0-9]{8}$/'],
            'email'              => ['nullable', 'email', 'max:255'],
            'adresse'            => ['nullable', 'string'],
            // id_avocat : optionnel, uniquement pour créer/modifier l'avocat de la partie
            'id_avocat'          => ['nullable', 'exists:avocats,id'],
            'id_type_partie'     => ['required', 'exists:type_parties,id'],
            'date_entree'        => ['required', 'date'],
        ]);

        if ($request->filled('partie_id')) {
            // ── Partie existante ──────────────────────────────────────────
            $partie = Partie::findOrFail($request->partie_id);

            // Si l'utilisateur a explicitement choisi un nouvel avocat, on met à jour la partie
            if ($request->filled('id_avocat') && $partie->id_avocat != $request->id_avocat) {
                $partie->update(['id_avocat' => $request->id_avocat]);
            }
        } else {
            // ── Nouvelle partie ───────────────────────────────────────────
            // L'avocat est stocké directement sur la partie (RG : lien permanent)
            $partie = Partie::firstOrCreate(
                ['identifiant_unique' => $request->identifiant_unique],
                [
                    'nom_partie'    => $request->nom_partie,
                    'type_personne' => $request->type_personne ?? 'Physique',
                    'telephone'     => $request->telephone,
                    'email'         => $request->email,
                    'adresse'       => $request->adresse,
                    'id_avocat'     => $request->id_avocat, // lié à la partie, pas au dossier
                ]
            );
        }

        // Vérifier que cette partie n'est pas déjà dans le dossier avec ce rôle
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

        // La table pivot dossier_parties ne stocke PAS id_avocat (c'est sur la partie)
        DossierPartie::create([
            'id_dossier'     => $dossier->id,
            'id_partie'      => $partie->id,
            'id_type_partie' => $request->id_type_partie,
            'date_entree'    => $request->date_entree,
        ]);

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-parties')
            ->with('success', "Partie « {$partie->nom_partie} » ajoutée au dossier.");
    }

    /**
     * Modifier le rôle et la date d'entrée d'une partie dans un dossier.
     * L'avocat se modifie depuis la fiche de la partie elle-même.
     */
    public function update(Request $request, DossierJudiciaire $dossier, DossierPartie $partie): RedirectResponse
    {
        $this->authorize('update', $dossier);

        $request->validate([
            'id_type_partie' => ['required', 'exists:type_parties,id'],
            'date_entree'    => ['required', 'date'],
        ]);

        $partie->update($request->only(['id_type_partie', 'date_entree']));

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