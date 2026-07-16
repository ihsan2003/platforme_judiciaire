<?php

namespace App\Http\Controllers;

use App\Http\Requests\Jugements\StoreJugementRequest;
use App\Http\Requests\Jugements\UpdateJugementRequest;
use App\Models\Jugement;
use App\Models\DossierTribunal;
use App\Models\Juge;
use App\Models\Partie;
use App\Models\DossierPartie;
use App\Models\DegreeJuridiction;
use App\Models\StatutExecution;
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
                'dossierTribunal.degre',
                'juge',
                'parties',
                'createdBy',
                'recours',
                'executions.statut',
            ])

            // Filtre juge
            ->when(request('juge'), function ($q, $v) {
                $q->where('id_juge', $v);
            })

            // Filtre définitif
            ->when(request('definitif') === 'oui', function ($q) {
                $q->where('est_definitif', true);
            })

            ->when(request('definitif') === 'non', function ($q) {
                $q->where('est_definitif', false);
            })

            // Filtre degré
            ->when(request('degre'), function ($q, $v) {
                $q->whereHas('dossierTribunal.degre', function ($query) use ($v) {
                    $query->where('id', $v);
                });
            })

            // Filtre position de l'institution (مع / ضد / جزئي)
            // On s'appuie sur jugement_parties.id_position_institution
            // (renseigné uniquement sur la ligne de la partie est_entraide = true),
            // et non plus sur une déduction via montant_condamne.
            ->when(in_array(request('position'), ['pour', 'contre', 'partiel']), function ($q) {
                $labelParPosition = ['pour' => 'مع', 'contre' => 'ضد', 'partiel' => 'جزئي'];
                $label = $labelParPosition[request('position')];

                $q->whereExists(function ($sub) use ($label) {
                    $sub->selectRaw('1')
                        ->from('jugement_parties')
                        ->join('parties', 'jugement_parties.id_partie', '=', 'parties.id')
                        ->join('position_institutions', 'jugement_parties.id_position_institution', '=', 'position_institutions.id')
                        ->whereColumn('jugement_parties.id_jugement', 'jugements.id')
                        ->where('parties.est_entraide', true)
                        ->where('position_institutions.position', $label);
                });
            })

            ->sortable([
                'date' => 'date_jugement',

                'dossier' => fn($q, $dir) => $q->orderBy(
                    \App\Models\DossierJudiciaire::select('numero_dossier_tribunal')
                        ->join('dossier_tribunaux', 'dossier_tribunaux.id_dossier', '=', 'dossier_judiciaires.id')
                        ->whereColumn('dossier_tribunaux.id', 'jugements.id_dossier_tribunal')
                        ->limit(1),
                    $dir
                ),

                'tribunal' => fn($q, $dir) => $q->orderBy(
                    \App\Models\Tribunal::select('nom_tribunal')
                        ->join('dossier_tribunaux', 'dossier_tribunaux.id_tribunal', '=', 'tribunaux.id')
                        ->whereColumn('dossier_tribunaux.id', 'jugements.id_dossier_tribunal')
                        ->limit(1),
                    $dir
                ),

                'degre' => fn($q, $dir) => $q->orderBy(
                    \App\Models\DegreeJuridiction::select('degre_juridiction')
                        ->join('dossier_tribunaux', 'dossier_tribunaux.id_degre', '=', 'degre_juridictions.id')
                        ->whereColumn('dossier_tribunaux.id', 'jugements.id_dossier_tribunal')
                        ->limit(1),
                    $dir
                ),

                'juge' => fn($q, $dir) => $q->orderBy(
                    Juge::select('nom_complet')
                        ->whereColumn('juges.id', 'jugements.id_juge'),
                    $dir
                ),

                'position' => fn($q, $dir) => $q->orderBy(
                    \App\Models\PositionInstitution::select('position')
                        ->join('jugement_parties', 'jugement_parties.id_position_institution', '=', 'position_institutions.id')
                        ->join('parties', 'jugement_parties.id_partie', '=', 'parties.id')
                        ->whereColumn('jugement_parties.id_jugement', 'jugements.id')
                        ->where('parties.est_entraide', true)
                        ->limit(1),
                    $dir
                ),

                'execution' => fn($q, $dir) => $q->orderBy(
                    StatutExecution::query()
                        ->select('statut_executions.statut_execution')
                        ->join(
                            'executions',
                            'executions.statut_execution',
                            '=',
                            'statut_executions.id'
                        )
                        ->whereColumn('executions.id_jugement', 'jugements.id')
                        ->limit(1),
                    $dir
                ),

                'definitif' => 'est_definitif',

            ], 'date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'      => Jugement::count(),
            'definitifs' => Jugement::where('est_definitif', true)->count(),
            'en_appel'   => Jugement::whereHas('recours')->count(),
            'executes'   => Jugement::whereHas('executions')->count(),
        ];

        // ─── Répartition pour / contre / partiel (source: position_institutions) ──
        $positionStats = \App\Models\JugementPartie::query()
            ->join('parties', 'jugement_parties.id_partie', '=', 'parties.id')
            ->join('position_institutions', 'jugement_parties.id_position_institution', '=', 'position_institutions.id')
            ->where('parties.est_entraide', true)
            ->selectRaw('position_institutions.position, COUNT(*) as total')
            ->groupBy('position_institutions.position')
            ->pluck('total', 'position');

        $stats['pour']    = (int) ($positionStats['مع'] ?? 0);
        $stats['contre']  = (int) ($positionStats['ضد'] ?? 0);
        $stats['partiel'] = (int) ($positionStats['جزئي'] ?? 0);

        // Positions indexées par id, pour affichage du badge dans le tableau
        $positionsParId = \App\Models\PositionInstitution::all()->keyBy('id');

        $juges = Juge::orderBy('nom_complet')->get();

        // IMPORTANT
        $degres = DegreeJuridiction::orderBy('degre_juridiction')->get();

        return view('jugements.index', compact(
            'jugements',
            'stats',
            'juges',
            'degres',
            'positionsParId'
        ));
    }

    // ─────────────────────────────────────────
    // CREATE (filtré correctement)
    // ─────────────────────────────────────────
    public function create()
    {
        $dossierId = request('dossier_id');

        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal', 'audiences.typeAudience'])
            ->when($dossierId, fn($q) => $q->where('id_dossier', $dossierId))
            ->whereHas('dossier', fn($q) => $q->actifs())
            ->get()
            ->filter(function ($dt) {
                return $dt->peutAvoirJugement()
                    || ($dt->estOuverte() && $dt->jugements()->doesntExist());
            });

        $juges              = Juge::with('tribunal')->orderBy('nom_complet')->get();
        $positionsInstitution = \App\Models\PositionInstitution::orderBy('position')->get();

        // Parties du dossier si dossier_id fourni
        $partiesDossier = collect();
        if ($dossierId) {
            $partiesDossier = \App\Models\DossierPartie::with(['partie', 'typePartie'])
                ->where('id_dossier', $dossierId)
                ->get();
        }

        $defaultDossierTribunalId = null;
        if ($dossierId) {
            $defaultDossierTribunalId = $dossierTribunaux
                ->filter(fn($dt) => $dt->estOuverte())
                ->sortByDesc('date_debut')
                ->first()
                ?->id;
        }

        return view('jugements.create', compact(
            'dossierTribunaux',
            'juges',
            'partiesDossier',
            'positionsInstitution',
            'defaultDossierTribunalId'
        ));
    }
    // ─────────────────────────────────────────
    // STORE (sécurité obligatoire)
    // ─────────────────────────────────────────
    public function store(StoreJugementRequest $request)
    {
        $dossierTribunal = DossierTribunal::with([
            'audiences.typeAudience',
            'dossier.dossierParties.typePartie',
            'dossier.dossierParties.partie',
            'jugements',
        ])->findOrFail($request->id_dossier_tribunal);

        // ── RG01 : vérifier les parties ───────────────────────────
        if (! $dossierTribunal->dossier->peutAvoirAudience()) {
            $manquants = implode('" et "', $dossierTribunal->dossier->typesPartiesManquants());
            return back()->with('error',
                "يتعذر إنشاء حكم: الصفة \"{$manquants}\" غير متوفرة."
            );
        }

        // ── RG03 : audience الحكم obligatoire ─────────────────────
        if ($dossierTribunal->audiences->isEmpty()) {
            return back()->with('error',
                'يتعذر إنشاء حكم: لا توجد أي جلسة لهذا الملف/المحكمة.'
            );
        }

        $audienceHoukm = $dossierTribunal->audienceHoukm();

        if (! $audienceHoukm) {
            return back()->with('error',
                'يتعذر إنشاء حكم: لم يتم تسجيل أي جلسة من نوع "الحكم".'
            );
        }

        // ── RG03 : un seul jugement par instance ──────────────────
        if ($dossierTribunal->jugements->isNotEmpty()) {
            return back()->with('error',
                'يوجد بالفعل حكم مسجل لهذه الدرجة القضائية.'
            );
        }

        // ── RG02 : date = date audience الحكم ─────────────────────
        $dateAttendue = $audienceHoukm->date_audience->toDateString();

        if ($request->date_jugement !== $dateAttendue) {
            return back()->withErrors([
                'date_jugement' =>
                    "يجب أن يطابق تاريخ الحكم تاريخ جلسة \"الحكم\" ({$audienceHoukm->date_audience->format('d/m/Y')}).",
            ])->withInput();
        }

        if ($audienceHoukm->date_audience->isFuture()) {
            return back()->withErrors([
                'date_jugement' => 'لا يمكن أن يكون تاريخ الحكم في المستقبل.',
            ])->withInput();
        }

        $jugement = DB::transaction(function () use ($request, $dossierTribunal) {

            // ── Créer le jugement ──────────────────────────────────
            $jugement = Jugement::create([
                ...$request->safe()->except('parties', 'montants', 'position_institution_etab'),
                'created_by' => Auth::id(),
            ]);

            // ── Sync des parties avec position et montant ──────────
            if ($request->filled('parties')) {
                $syncData = collect($request->parties)
                    ->mapWithKeys(function ($id) use ($request) {
                        $partie     = \App\Models\Partie::find($id);
                        $positionId = $partie?->est_entraide
                            ? $request->input('position_institution_etab')
                            : null;

                        return [$id => [
                            'id_position_institution' => $positionId,
                            'montant_condamne'        => $request->montants[$id] ?? null,
                        ]];
                    })->all();

                $jugement->parties()->sync($syncData);
            }

            // ── Création automatique de la Finance ─────────────────
            $montants  = collect($request->montants ?? [])
                ->filter(fn($v) => is_numeric($v) && $v > 0);

            $montantTotal = $montants->sum();

            if ($montantTotal > 0) {

                // Identifier l'établissement
                $etabPartie = $dossierTribunal->dossier->dossierParties
                    ->first(fn($dp) => $dp->partie?->est_entraide);

                $etabId = $etabPartie?->partie?->id;

                // Position choisie
                $positionId = $request->input('position_institution_etab');
                $position   = \App\Models\PositionInstitution::find($positionId);

                $posLabel  = strtolower($position?->position ?? '');
                $estContre = str_contains($posLabel, 'contre') || str_contains($posLabel, 'partiel');

                // Ventilation des montants
                $montantEtab = $etabId ? (float)($request->montants[$etabId] ?? 0) : 0;

                $montantAdverse = $montants
                    ->filter(fn($v, $k) => (string)$k !== (string)$etabId)
                    ->sum();

                \App\Models\Finance::create([
                    'id_jugement'               => $jugement->id,
                    'montant_reclame_demandeur' => $estContre ? null : $montantAdverse,
                    'montant_reclame_defendeur' => $estContre ? $montantEtab : null,
                    'montant_condamne'          => $montantTotal,
                    'montant_paye'              => 0,
                    'statut_paiement'           => 'En attente',
                ]);
            }

            return $jugement;
        });


        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'تم إنشاء الحكم بنجاح.');
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
    
            // Chargé une seule fois pour retrouver le libellé de "الصفة"
            // à partir de $partie->pivot->id_position_institution dans la vue.
            $positionsInstitution = \App\Models\PositionInstitution::pluck('position', 'id');
    
            return view('jugements.show', compact('jugement', 'positionsInstitution'));
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
        $partiesDossier = DossierPartie::with(['partie','typePartie'])->where('id_dossier', $jugement->dossierTribunal->id_dossier)->get();

        return view('jugements.edit', compact(
            'jugement',
            'dossiers',
            'juges',
            'parties',
            'partiesLiees',
            'partiesDossier'
        ));
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────
    public function update(UpdateJugementRequest $request, Jugement $jugement)
    {
        DB::transaction(function () use ($request, $jugement) {

            $jugement->update(
                $request->safe()->except('parties', 'montants', 'position_institution_etab')
            );

            $syncData = collect($request->parties ?? [])
                ->mapWithKeys(function ($partieId) use ($request) {
                    $partie     = \App\Models\Partie::find($partieId);
                    $positionId = $partie?->est_entraide
                        ? $request->input('position_institution_etab')
                        : null;

                    return [$partieId => [
                        'id_position_institution' => $positionId,
                        'montant_condamne' => $request->montants[$partieId] ?? null,
                    ]];
                })->all();

            $jugement->parties()->sync($syncData);

            // ── Recalcul de la Finance après modification des montants ──
            $montantTotal = collect($request->montants ?? [])
                ->filter(fn($v) => is_numeric($v) && $v > 0)
                ->sum();

            if ($montantTotal > 0) {
                $jugement->finance()->updateOrCreate(
                    ['id_jugement' => $jugement->id],
                    ['montant_condamne' => $montantTotal]
                );
            }
        });


        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', 'تم تحديث الحكم بنجاح.');
    }

    // ─────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────
    public function destroy(Jugement $jugement)
    {
        if ($jugement->est_definitif && $jugement->executions()->exists()) {
            return redirect()
                ->route('jugements.show', $jugement)
                ->with('error', 'يتعذر حذف حكم نهائي تم تنفيذه بالفعل.');
        }

        $date = $jugement->date_jugement->format('d/m/Y');

        DB::transaction(function () use ($jugement) {
            $jugement->parties()->detach();
            $jugement->delete();
        });

        return redirect()
            ->route('jugements.index')
            ->with('success', "تم حذف الحكم بتاريخ {$date} بنجاح.");
    }
}