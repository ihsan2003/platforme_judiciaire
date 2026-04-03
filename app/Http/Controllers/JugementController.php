<?php
// app/Http/Controllers/JugementController.php

namespace App\Http\Controllers;

use App\Http\Requests\Jugements\StoreJugementRequest;
use App\Http\Requests\Jugements\UpdateJugementRequest;
use App\Models\Jugement;
use App\Models\DossierTribunal;
use App\Models\Juge;
use App\Models\Partie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JugementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────
    public function index()
    {
        $jugements = Jugement::with([
                'dossierTribunal.dossier',
                'dossierTribunal.tribunal',
                'juge',
                'createdBy',
                'recours',
                'executions.statut',
            ])
            ->when(request('definitif'), fn($q, $v) => $q->where('est_definitif', $v === 'oui'))
            ->when(request('juge'),      fn($q, $v) => $q->where('id_juge', $v))
            ->latest('date_jugement')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'           => Jugement::count(),
            'definitifs'      => Jugement::where('est_definitif', true)->count(),
            'en_appel'        => Jugement::whereHas('recours')->count(),
            'executes'        => Jugement::whereHas('executions')->count(),
        ];

        $juges = Juge::orderBy('nom_complet')->get();

        return view('jugements.index', compact('jugements', 'stats', 'juges'));
    }

    // ─────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────
    public function create()
    {
        // Uniquement les dossiers_tribunaux dont le dossier est actif
        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal'])
            ->whereHas('dossier', fn($q) => $q->actifs())
            ->get();

        $juges  = Juge::with('tribunal')->orderBy('nom_complet')->get();
        $parties = Partie::orderBy('nom_partie')->get();

        return view('jugements.create', compact('dossierTribunaux', 'juges', 'parties'));
    }

    // ─────────────────────────────────────────
    // STORE — transaction + sync parties pivot
    // ─────────────────────────────────────────
    public function store(StoreJugementRequest $request)
    {
        $jugement = DB::transaction(function () use ($request) {
            $jugement = Jugement::create([
                ...$request->safe()->except('parties', 'montants'),
                'created_by' => Auth::id(),
            ]);

            // Sync parties avec montant_condamne optionnel par partie
            if ($request->filled('parties')) {
                $syncData = collect($request->parties)->mapWithKeys(function ($partieId) use ($request) {
                    return [$partieId => [
                        'id_position_institution' => null,
                        'montant_condamne'        => $request->montants[$partieId] ?? null,
                    ]];
                })->all();

                $jugement->parties()->sync($syncData);
            }

            return $jugement;
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Jugement du ' . $jugement->date_jugement->format('d/m/Y') . ' créé avec succès.');
    }

    // ─────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────
    public function show(Jugement $jugement)
    {
        $jugement->load([
            'dossierTribunal.dossier.typeAffaire',
            'dossierTribunal.dossier.statut',
            'dossierTribunal.tribunal',
            'juge',
            'createdBy',
            'parties.documents',
            'finance',
            'recours.typeRecours',
            'executions.statut',
            'executions.responsable',
        ]);

        return view('jugements.show', compact('jugement'));
    }

    // ─────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────
    public function edit(Jugement $jugement)
    {
        $jugement->load('parties');

        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal'])->get();
        $juges   = Juge::with('tribunal')->orderBy('nom_complet')->get();
        $parties = Partie::orderBy('nom_partie')->get();

        // IDs des parties déjà liées (pour pré-cocher)
        $partiesLiees = $jugement->parties->pluck('id')->toArray();

        return view('jugements.edit', compact(
            'jugement', 'dossierTribunaux', 'juges', 'parties', 'partiesLiees'
        ));
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────
    public function update(UpdateJugementRequest $request, Jugement $jugement)
    {
        DB::transaction(function () use ($request, $jugement) {
            $jugement->update($request->safe()->except('parties', 'montants'));

            $syncData = collect($request->parties ?? [])->mapWithKeys(function ($partieId) use ($request) {
                return [$partieId => [
                    'id_position_institution' => null,
                    'montant_condamne'        => $request->montants[$partieId] ?? null,
                ]];
            })->all();

            $jugement->parties()->sync($syncData);
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Jugement mis à jour avec succès.');
    }

    // ─────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────
    public function destroy(Jugement $jugement)
    {
        // Empêcher la suppression d'un jugement définitif exécuté
        if ($jugement->est_definitif && $jugement->executions()->exists()) {
            return redirect()
                ->route('jugements.show', $jugement)
                ->with('error', 'Impossible de supprimer un jugement définitif déjà exécuté.');
        }

        $date = $jugement->date_jugement->format('d/m/Y');

        DB::transaction(function () use ($jugement) {
            $jugement->parties()->detach();
            $jugement->delete();
        });

        return redirect()
            ->route('jugements.index')
            ->with('success', "Jugement du {$date} supprimé.");
    }
}
