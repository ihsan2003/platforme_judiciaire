<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Execution;
use App\Models\Jugement;
use App\Models\StatutExecution;
use App\Models\User;

class ExecutionController extends Controller
{
    public function index()
    {
        $executions = Execution::with(['jugement', 'statut', 'responsable'])
            ->latest('date_notification')
            ->paginate(10);

        return view('executions.index', compact('executions'));
    }

    public function create()
    {
        $jugements = Jugement::all();
        $statuts = StatutExecution::all();
        $responsables = User::all();

        return view('executions.create', compact('jugements', 'statuts', 'responsables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_jugement' => 'required|exists:jugements,id',
            'numero_dossier_execution' => 'required|string|max:255',
            'date_notification' => 'required|date',
            'statut_execution' => 'required|exists:statut_executions,id',
            'date_execution' => 'nullable|date|after_or_equal:date_notification',
            'responsable_id' => 'required|exists:users,id',
        ]);

        Execution::create($data);

        return redirect()->route('executions.index')
            ->with('success', 'Exécution créée avec succès');
    }

    public function show(Execution $execution)
    {
        $execution->load(['jugement', 'statut', 'responsable', 'finance']);

        return view('executions.show', compact('execution'));
    }

    public function edit(Execution $execution)
    {
        $jugements = Jugement::all();
        $statuts = StatutExecution::all();
        $responsables = User::all();

        return view('executions.edit', compact('execution', 'jugements', 'statuts', 'responsables'));
    }

    public function update(Request $request, Execution $execution)
    {
        $data = $request->validate([
            'id_jugement' => 'required|exists:jugements,id',
            'numero_dossier_execution' => 'required|string|max:255',
            'date_notification' => 'required|date',
            'statut_execution' => 'required|exists:statut_executions,id',
            'date_execution' => 'nullable|date|after_or_equal:date_notification',
            'responsable_id' => 'required|exists:users,id',
        ]);

        $execution->update($data);

        return redirect()->route('executions.index')
            ->with('success', 'Exécution mise à jour avec succès');
    }

    public function destroy(Execution $execution)
    {
        $execution->delete();

        return redirect()->route('executions.index')
            ->with('success', 'Exécution supprimée');
    }
}