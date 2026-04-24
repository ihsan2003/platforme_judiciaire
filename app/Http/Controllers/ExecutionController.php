<?php
// app/Http/Controllers/ExecutionController.php

namespace App\Http\Controllers;

use App\Http\Requests\Executions\StoreExecutionRequest;
use App\Http\Requests\Executions\UpdateExecutionRequest;
use App\Models\Execution;
use App\Models\Jugement;
use App\Models\StatutExecution;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class ExecutionController extends Controller
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
        $executions = Execution::with([
                'jugement.dossierTribunal.dossier',
                'jugement.dossierTribunal.tribunal',
                'jugement.juge',
                'statut',
                'responsable',
            ])
            ->when(request('statut'),      fn($q, $v) => $q->where('statut_execution', $v))
            ->when(request('responsable'), fn($q, $v) => $q->where('responsable_id', $v))
            ->latest('date_notification')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'       => Execution::count(),
            'en_cours'    => Execution::whereHas('statut', fn($q) => $q->where('statut_execution', 'En cours'))->count(),
            'terminees'   => Execution::whereNotNull('date_execution')->count(),
            'ce_mois'     => Execution::whereMonth('date_notification', now()->month)->count(),
        ];

        $statuts      = StatutExecution::orderBy('statut_execution')->get();
        $responsables = User::orderBy('name')->get();

        return view('executions.index', compact('executions', 'stats', 'statuts', 'responsables'));
    }

    // ─────────────────────────────────────────
    // CREATE — seulement les jugements définitifs non encore exécutés
    // ─────────────────────────────────────────
    public function create()
    {
        // Jugements définitifs sans exécution en cours ou terminée
        $jugements = Jugement::with(['dossierTribunal.dossier', 'dossierTribunal.tribunal', 'juge'])
            ->where('est_definitif', true)
            ->doesntHave('executions')
            ->orderBy('date_jugement', 'desc')
            ->get();

        $statuts      = StatutExecution::orderBy('statut_execution')->get();
        $responsables = User::orderBy('name')->get();

        return view('executions.create', compact('jugements', 'statuts', 'responsables'));
    }

    // ─────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────
    public function store(StoreExecutionRequest $request)
    {
        // Générer numéro automatique EXE-2026-001
        $last = Execution::latest('id')->first();

        $nextNumber = $last ? $last->id + 1 : 1;

        $numero = 'EXE-' . date('Y') . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $execution = Execution::create([
            ...$request->validated(),

            'numero_dossier_execution' => $numero,
            'responsable_id' => Auth::id(),
            'statut_execution' => 1, // statut "في الانتظار"
        ]);

        return redirect()
            ->route('executions.show', $execution)
            ->with('success', "Exécution « {$execution->numero_dossier_execution} » créée avec succès.");
    }

    // ─────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────
    public function show(Execution $execution)
    {
        $execution->load([
            'jugement.dossierTribunal.dossier.typeAffaire',
            'jugement.dossierTribunal.dossier.statut',
            'jugement.dossierTribunal.tribunal',
            'jugement.juge',
            'jugement.finance',
            'statut',
            'responsable',
        ]);

        $dossierParties = \App\Models\DossierPartie::with(['partie', 'typePartie', 'avocat'])
            ->where('id_dossier', $execution->jugement->dossierTribunal->id_dossier)
            ->get();

        $institution = $dossierParties->firstWhere('est_institution', true);
        $autresParties = $dossierParties->where('est_institution', false);

        return view('executions.show', compact('execution', 'dossierParties', 'institution', 'autresParties'));
    }

    // ─────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────
    public function edit(Execution $execution)
    {
        $jugements    = Jugement::with(['dossierTribunal.dossier', 'dossierTribunal.tribunal'])->get();
        $statuts      = StatutExecution::orderBy('statut_execution')->get();
        $responsables = User::orderBy('name')->get();

        return view('executions.edit', compact('execution', 'jugements', 'statuts', 'responsables'));
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────
    public function update(UpdateExecutionRequest $request, Execution $execution)
    {
        // ⛔ Bloquer si déjà terminée
        if ($execution->date_execution) {
            abort(403, 'Exécution déjà terminée.');
        }

        $data = $request->validated();

        // 🔒 Empêcher toute modification du jugement (sécurité supplémentaire)
        unset($data['id_jugement']);

        // ⛔ Empêcher retour en arrière du statut (ex: 3 = terminé)
        if (
            $execution->statut_execution == 3 &&
            isset($data['statut_execution']) &&
            $data['statut_execution'] != 3
        ) {
            return back()->withErrors([
                'statut_execution' => 'Impossible de revenir en arrière après terminaison.'
            ]);
        }

        // ⚡ Option intelligente (bonus)
        // Si date_execution est remplie → forcer statut = terminé
        if (!empty($data['date_execution'])) {
            $data['statut_execution'] = 3;
        }

        $execution->update($data);

        return redirect()
            ->route('executions.show', $execution)
            ->with('success', 'Exécution mise à jour.');
    }

    // ─────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────
    public function destroy(Execution $execution)
    {
        $numero = $execution->numero_dossier_execution;
        $execution->delete();

        return redirect()
            ->route('executions.index')
            ->with('success', "Exécution « {$numero} » supprimée.");
    }
}
