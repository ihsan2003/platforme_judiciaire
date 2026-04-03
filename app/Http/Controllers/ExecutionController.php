<?php
// app/Http/Controllers/ExecutionController.php

namespace App\Http\Controllers;

use App\Http\Requests\Executions\StoreExecutionRequest;
use App\Http\Requests\Executions\UpdateExecutionRequest;
use App\Models\Execution;
use App\Models\Jugement;
use App\Models\StatutExecution;
use App\Models\User;

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
        $execution = Execution::create($request->validated());

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
            'jugement.dossierTribunal.dossier.parties.typePartie',
            'jugement.dossierTribunal.tribunal',
            'jugement.juge',
            'jugement.finance',
            'statut',
            'responsable',
        ]);

        return view('executions.show', compact('execution'));
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
        $execution->update($request->validated());

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
