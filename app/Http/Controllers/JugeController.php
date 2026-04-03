<?php

namespace App\Http\Controllers;

use App\Models\Juge;
use App\Models\Tribunal;
use Illuminate\Http\Request;

class JugeController extends Controller
{
    /**
     * Liste des juges
     */
    public function index()
    {
        $juges = Juge::with('tribunal')->paginate(10);

        return view('juges.index', compact('juges'));
    }

    /**
     * Formulaire création
     */
    public function create()
    {
        $tribunaux = Tribunal::all();

        return view('juges.create', compact('tribunaux'));
    }

    /**
     * Enregistrer un juge
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom_complet' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'specialisation' => 'nullable|string|max:255',
            'id_tribunal' => 'required|exists:tribunaux,id',
        ]);

        Juge::create($request->only([
            'nom_complet',
            'grade',
            'specialisation',
            'id_tribunal'
        ]));

        return redirect()->route('juges.index')
                         ->with('success', 'Juge ajouté avec succès.');
    }

    /**
     * Afficher un juge
     */
    public function show(Juge $juge)
    {
        $juge->load(['tribunal', 'audiences', 'jugements']);

        return view('juges.show', compact('juge'));
    }

    /**
     * Formulaire modification
     */
    public function edit(Juge $juge)
    {
        $tribunaux = Tribunal::all();

        return view('juges.edit', compact('juge', 'tribunaux'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Juge $juge)
    {
        $request->validate([
            'nom_complet' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'specialisation' => 'nullable|string|max:255',
            'id_tribunal' => 'required|exists:tribunaux,id',
        ]);

        $juge->update($request->only([
            'nom_complet',
            'grade',
            'specialisation',
            'id_tribunal'
        ]));

        return redirect()->route('juges.index')
                         ->with('success', 'Juge modifié avec succès.');
    }

    /**
     * Suppression
     */
    public function destroy(Juge $juge)
    {
        $juge->delete();

        return redirect()->route('juges.index')
                         ->with('success', 'Juge supprimé.');
    }
}