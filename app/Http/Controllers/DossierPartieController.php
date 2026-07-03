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
            'identifiant_unique' => ['nullable', 'string', 'max:255'],
            'nom_partie'         => ['required_without:partie_id', 'nullable', 'string', 'max:255'],
            'type_personne' => ['required_without:partie_id','nullable','in:ذاتي,اعتباري'],
            'telephone'          => ['nullable', 'regex:/^(\+212|00212|0)(5|6|7)[0-9]{8}$/'],
            'email'              => ['nullable', 'email', 'max:255'],
            'adresse'            => ['nullable', 'string'],
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
            // NB : le CIN est optionnel. On ne peut s'en servir comme clé de
            // dédoublonnage (firstOrCreate) que lorsqu'il est renseigné, sinon
            // toutes les parties sans CIN finiraient fusionnées entre elles.
            if ($request->filled('identifiant_unique')) {
                $partie = Partie::firstOrCreate(
                    ['identifiant_unique' => $request->identifiant_unique],
                    [
                        'nom_partie'    => $request->nom_partie,
                        'type_personne' => $request->type_personne ?? 'ذاتي',
                        'telephone'     => $request->telephone,
                        'email'         => $request->email,
                        'adresse'       => $request->adresse,
                        'id_avocat'     => $request->id_avocat,
                    ]
                );
            } else {
                $partie = Partie::create([
                    'identifiant_unique' => null,
                    'nom_partie'    => $request->nom_partie,
                    'type_personne' => $request->type_personne ?? 'ذاتي',
                    'telephone'     => $request->telephone,
                    'email'         => $request->email,
                    'adresse'       => $request->adresse,
                    'id_avocat'     => $request->id_avocat,
                ]);
            }
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
                ->with('error', 'هذه الجهة مسجلة مسبقاً في هذا الملف بنفس الصفة.');
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
            ->with('success', "تمت إضافة الجهة « {$partie->nom_partie} » إلى الملف بنجاح.");
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
            ->with('success', 'تم تحديث معلومات الجهة بنجاح.');
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
            ->with('success', "تم حذف الجهة « {$nomPartie} » من الملف بنجاح.");
    }
}