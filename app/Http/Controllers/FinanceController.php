<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Jugement;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index()
    {
        $finances = Finance::with('jugement.dossierTribunal')->latest()->get();

        return view('finances.index', compact('finances'));
    }

    public function create()
    {
        $jugements = Jugement::with('dossierTribunal')->get();

        return view('finances.create', compact('jugements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_jugement' => 'required|exists:jugements,id',
            'montant_condamne' => 'required|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'date_paiement' => 'nullable|date',
            'est_solde' => 'boolean',
        ]);

        $validated['montant_paye'] = $validated['montant_paye'] ?? 0;

        Finance::create($validated);

        return back()->with('success', 'Finance ajoutée avec succès.');
    }

    public function show(Finance $finance)
    {
        $finance->load('jugement.dossierTribunal');

        return view('finances.show', compact('finance'));
    }

    public function edit(Finance $finance)
    {
        $jugements = Jugement::all();

        return view('finances.edit', compact('finance', 'jugements'));
    }

    public function update(Request $request, Finance $finance)
    {
        $request->validate([
            'id_jugement' => 'required|exists:jugements,id',
            'montant_reclame_demandeur' => 'nullable|numeric',
            'montant_reclame_defendeur' => 'nullable|numeric',
            'montant_condamne' => 'nullable|numeric',
            'montant_paye' => 'nullable|numeric',
            'date_paiement' => 'nullable|date',
            'statut_paiement' => 'nullable|string',
        ]);

        $finance->update($request->all());

        return redirect()->route('finances.index')
            ->with('success', 'Finance mise à jour');
    }

    public function destroy(Finance $finance)
    {
        $finance->delete();

        return redirect()->route('finances.index')
            ->with('success', 'Finance supprimée');
    }
}