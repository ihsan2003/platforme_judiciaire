<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jugement;
use App\Models\DossierTribunal;
use App\Models\Juge;
use App\Models\Partie;

class JugementController extends Controller
{
    public function index()
    {
        $jugements = Jugement::with(['dossierTribunal', 'juge', 'createdBy'])
            ->latest('date_jugement')
            ->paginate(10);

        return view('jugements.index', compact('jugements'));
    }

    public function create()
    {
        $dossiers = DossierTribunal::all();
        $juges = Juge::all();
        $parties = Partie::all();

        return view('jugements.create', compact('dossiers', 'juges', 'parties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_dossier_tribunal' => 'required|exists:dossier_tribunaux,id',
            'id_juge' => 'required|exists:juges,id',
            'date_jugement' => 'required|date',
            'contenu_dispositif' => 'required|string',
            'est_definitif' => 'boolean',
            'parties' => 'array',
            'parties.*' => 'exists:parties,id'
        ]);

        $data['created_by'] = auth()->id();

        $jugement = Jugement::create($data);

        // sync parties (pivot)
        if ($request->has('parties')) {
            $syncData = [];

            foreach ($request->parties as $partieId) {
                $syncData[$partieId] = [
                    'id_position_institution' => null,
                    'montant_condamne' => null
                ];
            }

            $jugement->parties()->sync($syncData);
        }

        return redirect()->route('jugements.index')
            ->with('success', 'Jugement créé avec succès');
    }

    public function show(Jugement $jugement)
    {
        $jugement->load(['dossierTribunal', 'juge', 'parties', 'finance', 'recours', 'executions']);

        return view('jugements.show', compact('jugement'));
    }

    public function edit(Jugement $jugement)
    {
        $dossiers = DossierTribunal::all();
        $juges = Juge::all();
        $parties = Partie::all();

        return view('jugements.edit', compact('jugement', 'dossiers', 'juges', 'parties'));
    }

    public function update(Request $request, Jugement $jugement)
    {
        $data = $request->validate([
            'id_dossier_tribunal' => 'required|exists:dossier_tribunaux,id',
            'id_juge' => 'required|exists:juges,id',
            'date_jugement' => 'required|date',
            'contenu_dispositif' => 'required|string',
            'est_definitif' => 'boolean',
            'parties' => 'array',
            'parties.*' => 'exists:parties,id'
        ]);

        $jugement->update($data);

        // sync parties
        $jugement->parties()->sync($request->parties ?? []);

        return redirect()->route('jugements.index')
            ->with('success', 'Jugement mis à jour avec succès');
    }

    public function destroy(Jugement $jugement)
    {
        $jugement->delete();

        return redirect()->route('jugements.index')
            ->with('success', 'Jugement supprimé');
    }
}