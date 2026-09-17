<?php
// app/Http/Controllers/ExecutionController.php

namespace App\Http\Controllers;

use App\Http\Requests\Executions\StoreExecutionRequest;
use App\Http\Requests\Executions\UpdateExecutionRequest;
use App\Http\Controllers\Concerns\AuthorizesViaDossier;
use App\Models\Execution;
use App\Models\Jugement;
use App\Models\Tribunal;
use App\Models\DossierJudiciaire;
use App\Models\StatutExecution;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class ExecutionController extends Controller
{

    use AuthorizesViaDossier; 

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->only('destroy');
    }

    // ─────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────
    public function index()
    {
        $executions = Execution::with([
                'jugement.dossierTribunal.tribunal',
                'jugement.juge',
                'jugement.parties',
                'statut',
                'responsable',
            ])

            // ══ JOIN pour permettre le tri des relations ══
            ->leftJoin(
                'jugements',
                'jugements.id',
                '=',
                'executions.id_jugement'
            )

            ->leftJoin(
                'dossier_tribunaux',
                'dossier_tribunaux.id',
                '=',
                'jugements.id_dossier_tribunal'
            )

            ->leftJoin(
                'dossier_judiciaires',
                'dossier_judiciaires.id',
                '=',
                'dossier_tribunaux.id_dossier'
            )

            ->leftJoin(
                'tribunaux',
                'tribunaux.id',
                '=',
                'dossier_tribunaux.id_tribunal'
            )

            ->select('executions.*')


            // ══ Recherche numéro exécution + tribunal + dossier ══
            ->when(request('search'), function ($q, $v) {

                $q->where(function ($query) use ($v) {

                    $query
                        ->where(
                            'executions.numero_dossier_execution',
                            'like',
                            "%{$v}%"
                        )

                        ->orWhere(
                            'dossier_judiciaires.numero_dossier_tribunal',
                            'like',
                            "%{$v}%"
                        )

                        ->orWhere(
                            'tribunaux.nom_tribunal',
                            'like',
                            "%{$v}%"
                        )

                        ->orWhereHas(
                            'jugement.juge',
                            function ($juge) use ($v) {

                                $juge->where(
                                    'nom_complet',
                                    'like',
                                    "%{$v}%"
                                );

                            }
                        );

                });

            })


            // ══ Filtre statut ══
            ->when(request('statut'), function ($q, $v) {

                $q->where(
                    'executions.statut_execution',
                    $v
                );

            })


            // ══ Filtre date notification ══
            ->when(request('date_notification'), function ($q, $v) {

                $q->whereDate(
                    'executions.date_notification',
                    $v
                );

            })


            // ══ Filtre date exécution ══
            ->when(request('date_execution'), function ($q, $v) {

                $q->whereDate(
                    'executions.date_execution',
                    $v
                );

            })


            // ══ Tri colonnes ══
            ->sortable([

                'numero' => 'executions.numero_dossier_execution',

                'dossier' => 'dossier_judiciaires.numero_dossier_tribunal',

                'jugement' => 'jugements.date_jugement',

                'tribunal' => 'tribunaux.nom_tribunal',


                'statut' => fn($q, $dir) => $q->orderBy(
                    StatutExecution::select('statut_execution')
                        ->whereColumn(
                            'statut_executions.id',
                            'executions.statut_execution'
                        ),
                    $dir
                ),


                'responsable' => fn($q, $dir) => $q->orderBy(
                    User::select('name')
                        ->whereColumn(
                            'users.id',
                            'executions.responsable_id'
                        ),
                    $dir
                ),


                'notification' => 'executions.date_notification',

                'execution' => 'executions.date_execution',

            ], 'notification', 'desc')


            ->paginate(15)

            ->withQueryString();


        $stats = [

            'total' => Execution::count(),


            'en_cours' => Execution::whereHas(
                'statut',
                fn($q) =>
                    $q->where('statut_execution', 'قيد التنفيذ')
            )->count(),


            'terminees' => Execution::whereHas(
                'statut',
                fn($q) =>
                    $q->where('statut_execution', 'تنفيذ كامل')
            )->count(),


            'ce_mois' => Execution::whereMonth(
                'date_notification',
                now()->month
            )->count(),

        ];


        $statuts = StatutExecution::orderBy(
            'statut_execution'
        )->get();


        $responsables = User::orderBy(
            'name'
        )->get();


        return view(
            'executions.index',
            compact(
                'executions',
                'stats',
                'statuts',
                'responsables'
            )
        );
    }

    // ─────────────────────────────────────────
    // CREATE 
    // ─────────────────────────────────────────
    public function create()
    {
        // Jugements définitifs sans exécution en cours ou terminée
        $jugements = Jugement::with(['dossierTribunal.dossier', 'dossierTribunal.tribunal', 'juge', 'parties'])
            ->where('est_definitif', true)
            ->doesntHave('executions')
            ->orderBy('date_jugement', 'desc')
            ->get();

        $statuts      = StatutExecution::orderBy('statut_execution')->get();
        $responsables = User::orderBy('name')->get();
        $selectedJugement = null;

        if (request('jugement_id')) {
            $selectedJugement = Jugement::with(['dossierTribunal.tribunal', 'parties'])
                ->find(request('jugement_id'));
        }

        return view('executions.create', compact('jugements', 'statuts', 'responsables','selectedJugement'));
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

        $jugement = \App\Models\Jugement::findOrFail($request->id_jugement);
        $this->authorizeDossier('update', $jugement);

        $execution = Execution::create([
            ...$request->validated(),

            'numero_dossier_execution' => $numero,
            'responsable_id' => Auth::id(),
            'statut_execution' => 1, // statut "في الانتظار"
        ]);


        return redirect()
            ->route('executions.show', $execution)
            ->with('success', "تم إنشاء ملف التنفيذ « {$execution->numero_dossier_execution} » بنجاح.");
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
            'jugement.parties',
            'statut',
            'responsable',
        ]);

        $dossierParties = \App\Models\DossierPartie::with(['partie.avocat', 'typePartie'])
            ->where('id_dossier', $execution->jugement->dossierTribunal->id_dossier)
            ->get();

        $institution = $dossierParties->first(fn($dp) => $dp->partie?->est_entraide);
        $autresParties = $dossierParties->filter(fn($dp) => !$dp->partie?->est_entraide);

        // ── RG : partie(s) concernée(s) par l'exécution ─────────────────
        // Si l'institution est condamnée ("ضد"), c'est elle qui est concernée.
        // Si elle est gagnante ("مع"), ce sont les autres parties — cochées
        // lors de la création du jugement — qui sont concernées.
        $estContreInstitution = $execution->jugement->estContreInstitution();
        $idsPartiesConcernees = $execution->jugement->partiesIdsConcerneesParExecution();

        $partiesConcernees = $dossierParties->filter(
            fn($dp) => $idsPartiesConcernees->contains($dp->partie?->id)
        );

        return view('executions.show', compact(
            'execution', 'dossierParties', 'institution', 'autresParties',
            'partiesConcernees', 'estContreInstitution'
        ));
    }

    // ─────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────
    public function edit(Execution $execution)
    {
        $this->authorizeDossier('view', $execution);

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
        $this->authorizeDossier('update', $execution);
        
        if ($execution->date_execution) {
            abort(403, 'التنفيذ منتهي بالفعل.');
        }

        $data = $request->validated();

        unset($data['id_jugement']);

        if (
            $execution->statut_execution == 3 &&
            isset($data['statut_execution']) &&
            $data['statut_execution'] != 3
        ) {
            return back()->withErrors([
                'statut_execution' => 'لا يمكن الرجوع إلى حالة سابقة بعد إنهاء التنفيذ.'
            ]);
        }

    
        if (!empty($data['date_execution'])) {
            $data['statut_execution'] = 3;
        }

        $execution->update($data);

        return redirect()
            ->route('executions.show', $execution)
            ->with('success', 'تم تحديث ملف التنفيذ بنجاح.');
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
            ->with('success', "تم حذف ملف التنفيذ « {$numero} » بنجاح.");
    }
}