<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DossierJudiciaire;
use App\Models\DossierPartie;
use App\Models\Partie;
use App\Models\PartieAvocat;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DossierPartieController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->only('destroy');
    }

    /**
     * Recherche de parties existantes par identifiant ou nom (AJAX).
     * Retourne aussi les avocats de la partie pour affichage informatif.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $parties = Partie::with('avocats')
            ->where(function ($query) use ($q) {
                $query->where('identifiant_unique', 'like', "%{$q}%")
                      ->orWhere('nom_partie', 'like', "%{$q}%");
            })
            ->orderBy('nom_partie')
            ->limit(10)
            ->get(['id', 'identifiant_unique', 'nom_partie', 'type_personne',
                'telephone', 'email', 'adresse']);

        // Inclure les noms des avocats pour affichage (lecture seule)
        $parties->transform(fn($p) => array_merge($p->toArray(), [
            'avocats_noms' => $p->avocats->pluck('nom_avocat')->implode('، '),
        ]));

        return response()->json($parties);
    }

    /**
     * Ajouter une partie existante ou nouvelle au dossier.
     *
     * RG : une partie peut avoir plusieurs avocats (partie_avocats). Ici on peut
     * affecter un premier avocat, pour ce dossier, éventuellement limité à un degré
     * de juridiction. D'autres avocats se gèrent ensuite depuis la colonne « المحامون ».
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
            'id_degre'           => ['nullable', 'exists:degre_juridictions,id'],
            'id_type_partie'     => ['required', 'exists:type_parties,id'],
            'date_entree'        => ['required', 'date'],
        ]);

        if ($request->filled('partie_id')) {
            // ── Partie existante ──────────────────────────────────────────
            $partie = Partie::findOrFail($request->partie_id);
        } else {
            // ── Nouvelle partie ───────────────────────────────────────────
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

        DossierPartie::create([
            'id_dossier'     => $dossier->id,
            'id_partie'      => $partie->id,
            'id_type_partie' => $request->id_type_partie,
            'date_entree'    => $request->date_entree,
        ]);

        // Premier avocat de la partie pour ce dossier (optionnel)
        if ($request->filled('id_avocat')) {
            $this->affecterAvocat($partie->id, (int) $request->id_avocat, $dossier->id, $request->input('id_degre'));
        }

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
        abort_unless($partie->id_dossier === $dossier->id, 403); 

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
        abort_unless($partie->id_dossier === $dossier->id, 403); 

        $nomPartie = $partie->partie->nom_partie;
        $partie->delete();

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-parties')
            ->with('success', "تم حذف الجهة « {$nomPartie} » من الملف بنجاح.");
    }

    /**
     * Affecter un avocat à une partie pour ce dossier, pour tous les degrés
     * ou pour un degré précis (1ère instance, appel, cassation…).
     */
    public function storeAvocat(Request $request, DossierJudiciaire $dossier, DossierPartie $partie): RedirectResponse
    {
        $this->authorize('update', $dossier);
        abort_unless($partie->id_dossier === $dossier->id, 403);

        $data = $request->validate([
            'id_avocat' => ['required', 'exists:avocats,id'],
            'id_degre'  => ['nullable', 'exists:degre_juridictions,id'],
        ], [
            'id_avocat.required' => 'يرجى اختيار المحامي.',
        ]);

        $cree = $this->affecterAvocat(
            $partie->id_partie,
            (int) $data['id_avocat'],
            $dossier->id,
            $data['id_degre'] ?? null
        );

        $redirect = redirect()->route('dossiers.show', $dossier)->withFragment('tab-parties');

        return $cree
            ? $redirect->with('success', 'تم تعيين المحامي بنجاح.')
            : $redirect->with('error', 'هذا المحامي معيّن مسبقاً لهذه الجهة بنفس الدرجة.');
    }

    /**
     * Retirer un avocat d'une partie dans ce dossier.
     * Seules les affectations propres à ce dossier sont supprimables ici ;
     * les affectations générales se gèrent depuis la fiche de la partie ou de l'avocat.
     */
    public function destroyAvocat(DossierJudiciaire $dossier, DossierPartie $partie, PartieAvocat $affectation): RedirectResponse
    {
        $this->authorize('update', $dossier);
        abort_unless($partie->id_dossier === $dossier->id, 403);
        abort_unless(
            $affectation->id_partie === $partie->id_partie && $affectation->id_dossier === $dossier->id,
            403
        );

        $affectation->delete();

        return redirect()
            ->route('dossiers.show', $dossier)
            ->withFragment('tab-parties')
            ->with('success', 'تم سحب المحامي من هذه الجهة.');
    }

    /**
     * Crée l'affectation si elle n'existe pas déjà (même partie, avocat, dossier, degré).
     * Retourne false si elle existait déjà.
     */
    private function affecterAvocat(int $idPartie, int $idAvocat, int $idDossier, $idDegre): bool
    {
        $idDegre = $idDegre ?: null;

        $existe = PartieAvocat::where('id_partie', $idPartie)
            ->where('id_avocat', $idAvocat)
            ->where('id_dossier', $idDossier)
            ->when(
                $idDegre,
                fn ($q) => $q->where('id_degre', $idDegre),
                fn ($q) => $q->whereNull('id_degre')
            )
            ->exists();

        if ($existe) {
            return false;
        }

        PartieAvocat::create([
            'id_partie'  => $idPartie,
            'id_avocat'  => $idAvocat,
            'id_dossier' => $idDossier,
            'id_degre'   => $idDegre,
        ]);

        return true;
    }
}
