<?php

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
            ->latest('date_jugement')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'      => Jugement::count(),
            'definitifs' => Jugement::where('est_definitif', true)->count(),
            'en_appel'   => Jugement::whereHas('recours')->count(),
            'executes'   => Jugement::whereHas('executions')->count(),
        ];

        $juges = Juge::orderBy('nom_complet')->get();

        return view('jugements.index', compact('jugements', 'stats', 'juges'));
    }

    // ─────────────────────────────────────────
    // CREATE (filtré correctement)
    // ─────────────────────────────────────────
    public function create()
    {
        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal', 'audiences.typeAudience'])
            ->whereHas('dossier', fn($q) => $q->actifs())
            ->get()
            ->filter(fn($dt) => $dt->peutAvoirJugement());

        $juges   = Juge::with('tribunal')->orderBy('nom_complet')->get();
        $parties = Partie::orderBy('nom_partie')->get();

        return view('jugements.create', compact(
            'dossierTribunaux',
            'juges',
            'parties'
        ));
    }

    // ─────────────────────────────────────────
    // STORE (sécurité obligatoire)
    // ─────────────────────────────────────────
    public function store(StoreJugementRequest $request)
    {
        $dossierTribunal = DossierTribunal::with('audiences.typeAudience')
            ->findOrFail($request->id_dossier_tribunal);

        // 🔒 sécurité backend
        if (!$dossierTribunal->peutAvoirJugement()) {
            return redirect()->back()
                ->with('error', "Impossible de créer un jugement sans audience de type 'الحكم'.");
        }

        $jugement = DB::transaction(function () use ($request) {

            $jugement = Jugement::create([
                ...$request->safe()->except('parties', 'montants'),
                'created_by' => Auth::id(),
            ]);

            // Sync parties
            if ($request->filled('parties')) {
                $syncData = collect($request->parties)->mapWithKeys(function ($partieId) use ($request) {
                    return [$partieId => [
                        'id_position_institution' => null,
                        'montant_condamne' => $request->montants[$partieId] ?? null,
                    ]];
                })->all();

                $jugement->parties()->sync($syncData);
            }

            return $jugement;
        });

        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'Jugement créé avec succès.');
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

        $dossiers = DossierTribunal::with(['dossier', 'tribunal'])->get();
        $juges   = Juge::with('tribunal')->orderBy('nom_complet')->get();
        $parties = Partie::orderBy('nom_partie')->get();

        $partiesLiees = $jugement->parties->pluck('id')->toArray();

        return view('jugements.edit', compact(
            'jugement',
            'dossiers',
            'juges',
            'parties',
            'partiesLiees'
        ));
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────
    public function update(UpdateJugementRequest $request, Jugement $jugement)
    {
        DB::transaction(function () use ($request, $jugement) {

            $jugement->update(
                $request->safe()->except('parties', 'montants')
            );

            $syncData = collect($request->parties ?? [])
                ->mapWithKeys(function ($partieId) use ($request) {
                    return [$partieId => [
                        'id_position_institution' => null,
                        'montant_condamne' => $request->montants[$partieId] ?? null,
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