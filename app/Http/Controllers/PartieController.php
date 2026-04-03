<?php
// app/Http/Controllers/PartieController.php

namespace App\Http\Controllers;

use App\Models\Partie;
use App\Models\TypePartie;
use App\Models\Avocat;
use App\Http\Requests\Parties\StorePartieRequest;
use App\Http\Requests\Parties\UpdatePartieRequest;
use Illuminate\Http\Request;

class PartieController extends Controller
{
    /**
     * Sécuriser toutes les routes avec le middleware auth
     * et autoriser les actions via Policy
     */
    public function __construct()
    {
        $this->middleware('auth');
        // $this->authorizeResource(Partie::class, 'partie'); // décommente si tu as une Policy
    }

    // =========================================================
    // INDEX — Liste des parties avec recherche et pagination
    // =========================================================
    public function index(Request $request)
    {
        $query = Partie::query();

        // Recherche dynamique
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom_partie', 'like', "%{$search}%")
                  ->orWhere('identifiant_unique', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre par type de personne
        if ($request->filled('type_personne')) {
            $query->where('type_personne', $request->type_personne);
        }

        // Pagination : 15 résultats par page, on garde les filtres dans les liens
        $parties = $query->latest()->paginate(15)->withQueryString();

        return view('parties.index', compact('parties'));
    }

    // =========================================================
    // CREATE — Formulaire de création
    // =========================================================
    public function create()
    {
        // On passe les données nécessaires au formulaire
        $typesPartie = TypePartie::orderBy('type_partie')->get();
        $avocats     = Avocat::orderBy('nom_avocat')->get();

        return view('parties.create', compact('typesPartie', 'avocats'));
    }

    // =========================================================
    // STORE — Validation + Enregistrement
    // =========================================================
    public function store(StorePartieRequest $request)
    {
        // La validation est dans StorePartieRequest (voir section 4)
        // $request->validated() retourne uniquement les champs validés
        $partie = Partie::create($request->validated());

        return redirect()
            ->route('parties.show', $partie)
            ->with('success', 'La partie a été créée avec succès.');
    }

    // =========================================================
    // SHOW — Détail d'une partie (Route Model Binding)
    // =========================================================
    public function show(Partie $partie)
    {
        // Eager loading pour éviter le problème N+1
        $partie->load([
            'dossiers.typeAffaire',
            'dossiers.statutDossier',
            'documents',
            'jugements',
        ]);

        return view('parties.show', compact('partie'));
    }

    // =========================================================
    // EDIT — Formulaire de modification
    // =========================================================
    public function edit(Partie $partie)
    {
        $typesPartie = TypePartie::orderBy('type_partie')->get();
        $avocats     = Avocat::orderBy('nom_avocat')->get();

        return view('parties.edit', compact('partie', 'typesPartie', 'avocats'));
    }

    // =========================================================
    // UPDATE — Validation + Mise à jour
    // =========================================================
    public function update(UpdatePartieRequest $request, Partie $partie)
    {
        $partie->update($request->validated());

        return redirect()
            ->route('parties.show', $partie)
            ->with('success', 'La partie a été modifiée avec succès.');
    }

    // =========================================================
    // DESTROY — Suppression (soft delete car SoftDeletes est actif)
    // =========================================================
    public function destroy(Partie $partie)
    {
        $partie->delete(); // Soft delete — l'enregistrement reste en BDD

        return redirect()
            ->route('parties.index')
            ->with('success', 'La partie a été supprimée.');
    }
}