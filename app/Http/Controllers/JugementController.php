<?php

namespace App\Http\Controllers;

use App\Http\Requests\Jugements\StoreJugementRequest;
use App\Http\Requests\Jugements\UpdateJugementRequest;
use App\Http\Controllers\Concerns\AuthorizesViaDossier;
use App\Models\Jugement;
use App\Models\TypeJugement;
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
        $jugements = Jugement::with([
                'dossierTribunal.dossier',
                'dossierTribunal.tribunal',
                'dossierTribunal.degre',
                'juge',
                'typeJugement',
                'parties',
                'createdBy',
                'recours',
                'executions.statut',
            ])

            // Filtre type de jugement (تمهيدي / فاصل في الموضوع)
            ->when(request('type_jugement'), fn($q, $v) =>
                $q->where('id_type_jugement', $v))

            ->when(request('recherche'), function ($q, $v) {
                $q->where(function ($sub) use ($v) {
                    $sub->whereHas('juge', fn($q) => $q->where('nom_complet', 'like', "%{$v}%"))
                        ->orWhereHas('dossierTribunal.tribunal', fn($q) => $q->where('nom_tribunal', 'like', "%{$v}%"))
                        ->orWhereHas('dossierTribunal.dossier', fn($q) => $q->where('numero_dossier_tribunal', 'like', "%{$v}%"));
                });
            })
            ->when(request('date_jugement'), fn($q, $v) =>
                $q->whereDate('date_jugement', $v))

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
                    \App\Models\DossierJudiciaire::select('dossier_judiciaires.numero_dossier_tribunal')
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
            'total'        => Jugement::count(),
            'definitifs'   => Jugement::where('est_definitif', true)->count(),
            'en_appel'     => Jugement::whereHas('recours')->count(),
            'executes'     => Jugement::whereHas('executions')->count(),
            'preparatoires' => Jugement::preparatoires()->count(),
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

        // IMPORTANT
        $degres = DegreeJuridiction::orderBy('degre_juridiction')->get();

        $typesJugement = TypeJugement::orderBy('id')->get();

        return view('jugements.index', compact(
            'jugements',
            'stats',
            'degres',
            'positionsParId',
            'typesJugement'
        ));
    }

    // ─────────────────────────────────────────
    // CREATE 
    // ─────────────────────────────────────────
    public function create()
    {
        $dossierId = request('dossier_id');

        $dossierTribunaux = DossierTribunal::with(['dossier', 'tribunal', 'degre', 'audiences.typeAudience', 'jugements'])
            ->when($dossierId, fn($q) => $q->where('id_dossier', $dossierId))
            ->whereHas('dossier', fn($q) => $q->actifs())
            ->get()
            ->filter(function ($dt) {
                // Un jugement sur le fond existant verrouille l'instance.
                if ($dt->aUnJugementAuFond()) {
                    return false;
                }

                // Instance ouverte : un jugement préparatoire reste possible à tout moment ;
                // le jugement sur le fond exige une audience الحكم (contrôlé côté formulaire/store).
                return $dt->estOuverte() || $dt->audienceHoukmPourJugementAuFond() !== null;
            });

        $juges              = Juge::with('tribunal')->orderBy('nom_complet')->get();
        $positionsInstitution = \App\Models\PositionInstitution::orderBy('position')->get();
        $typesJugement      = TypeJugement::orderBy('id')->get();

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
            'typesJugement',
            'defaultDossierTribunalId'
        ));
    }
    // ─────────────────────────────────────────
    // STORE 
    // ─────────────────────────────────────────
    public function store(StoreJugementRequest $request)
    {
        $dossierTribunal = DossierTribunal::with([
            'audiences.typeAudience',
            'dossier.dossierParties.typePartie',
            'dossier.dossierParties.partie',
            'jugements',
        ])->findOrFail($request->id_dossier_tribunal);

        $this->authorizeDossier('update', $dossierTribunal);

        // ── RG01 : vérifier les parties ───────────────────────────
        if (! $dossierTribunal->dossier->peutAvoirAudience()) {
            $manquants = implode('" et "', $dossierTribunal->dossier->typesPartiesManquants());
            return back()->with('error',
                "يتعذر إنشاء حكم: الصفة \"{$manquants}\" غير متوفرة."
            );
        }

        $typeJugement    = TypeJugement::findOrFail($request->id_type_jugement);
        $estPreparatoire = $typeJugement->type_jugement === TypeJugement::PREPARATOIRE;

        // ── RG03 : au moins une audience dans l'instance ──────────
        if ($dossierTribunal->audiences->isEmpty()) {
            return back()->with('error',
                'يتعذر إنشاء حكم: لا توجد أي جلسة لهذا الملف/المحكمة.'
            );
        }

        // ── RG03 : un seul jugement sur le fond par instance ──────
        // (les jugements préparatoires, eux, peuvent être plusieurs)
        if ($dossierTribunal->aUnJugementAuFond()) {
            return back()->with('error',
                'يوجد بالفعل حكم مسجل لهذه الدرجة القضائية.'
            );
        }

        $dernierPrep = $dossierTribunal->dernierJugementPreparatoire();

        if ($estPreparatoire) {

            // ── Jugement préparatoire : l'instance doit être ouverte ──
            if (! $dossierTribunal->estOuverte()) {
                return back()->with('error',
                    'يتعذر تسجيل حكم تمهيدي: هذه الدرجة القضائية مغلقة.'
                );
            }

            // ── RG02-bis : date = date d'une audience de l'instance
            //    (tous types d'audience confondus) ──────────────────
            $audienceDuJugement = $dossierTribunal->audiences
                ->first(fn($a) => $a->date_audience->toDateString() === $request->date_jugement);

            if (! $audienceDuJugement) {
                return back()->withErrors([
                    'date_jugement' =>
                        'يجب أن يطابق تاريخ الحكم التمهيدي تاريخ إحدى جلسات هذه الدرجة القضائية.',
                ])->withInput();
            }

            // ── Postérieur au jugement préparatoire précédent ───────
            if ($dernierPrep && $request->date_jugement <= $dernierPrep->date_jugement->toDateString()) {
                return back()->withErrors([
                    'date_jugement' =>
                        "يجب أن يكون تاريخ الحكم التمهيدي لاحقاً لتاريخ الحكم التمهيدي السابق ({$dernierPrep->date_jugement->format('d/m/Y')}).",
                ])->withInput();
            }

        } else {

            // ── Jugement sur le fond : audience الحكم obligatoire, ──
            //    postérieure au dernier jugement préparatoire
            $audienceHoukm = $dossierTribunal->audienceHoukmPourJugementAuFond();

            if (! $audienceHoukm) {
                $message = $dernierPrep
                    ? "يتعذر إنشاء حكم: يجب تسجيل جلسة جديدة من نوع \"الحكم\" بعد الحكم التمهيدي بتاريخ {$dernierPrep->date_jugement->format('d/m/Y')}."
                    : 'يتعذر إنشاء حكم: لم يتم تسجيل أي جلسة من نوع "الحكم".';

                return back()->with('error', $message);
            }

            // ── RG02 : date = date audience الحكم ─────────────────
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
        }

        $jugement = DB::transaction(function () use ($request, $dossierTribunal, $estPreparatoire) {

            // ── Créer le jugement ──────────────────────────────────
            $jugement = Jugement::create([
                ...$request->safe()->except('parties', 'montants', 'position_institution_etab'),
                // Un jugement préparatoire ne peut jamais être "définitif"
                'est_definitif' => $estPreparatoire ? false : $request->boolean('est_definitif'),
                'created_by' => Auth::id(),
            ]);

            // ── Jugement préparatoire : pas de parties/montants/finance ──
            if ($estPreparatoire) {
                return $jugement;
            }

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
                            // Le montant n'est jamais obligatoire (مع أو ضد) :
                            // 0 par défaut si aucune valeur n'a été saisie.
                            'montant_condamne'        => $request->montants[$id] ?? 0,
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
                    'statut_paiement'           => 'في الانتظار',
                ]);
            }

            return $jugement;
        });


        return redirect()
            ->route('jugements.show', $jugement)
            ->with('success', $estPreparatoire
                ? 'تم تسجيل الحكم التمهيدي بنجاح. يمكنكم متابعة تسجيل الجلسات.'
                : 'تم إنشاء الحكم بنجاح.');
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
        $this->authorizeDossier('view', $jugement);

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
        $this->authorizeDossier('update', $jugement);
        
        $estPreparatoire = $jugement->estPreparatoire();

        // Jugement préparatoire : la date doit rester celle d'une audience de l'instance
        if ($estPreparatoire
            && $request->date_jugement !== $jugement->date_jugement->toDateString()
            && ! $jugement->dossierTribunal->audiences()
                    ->whereDate('date_audience', $request->date_jugement)->exists()
        ) {
            return back()->withErrors([
                'date_jugement' =>
                    'يجب أن يطابق تاريخ الحكم التمهيدي تاريخ إحدى جلسات هذه الدرجة القضائية.',
            ])->withInput();
        }

        DB::transaction(function () use ($request, $jugement, $estPreparatoire) {

            // Le type de jugement n'est pas modifiable après création (il conditionne
            // parties, finance, recours et clôture de l'instance).
            $jugement->update([
                ...$request->safe()->except(
                    'parties', 'montants', 'position_institution_etab', 'id_type_jugement'
                ),
                // Un jugement préparatoire ne peut jamais être "définitif"
                'est_definitif' => $estPreparatoire ? false : $request->boolean('est_definitif'),
            ]);

            // Jugement préparatoire : ni parties/positions, ni montants, ni finance
            if ($estPreparatoire) {
                return;
            }

            $syncData = collect($request->parties ?? [])
                ->mapWithKeys(function ($partieId) use ($request) {
                    $partie     = \App\Models\Partie::find($partieId);
                    $positionId = $partie?->est_entraide
                        ? $request->input('position_institution_etab')
                        : null;

                    return [$partieId => [
                        'id_position_institution' => $positionId,
                        // Le montant n'est jamais obligatoire (مع أو ضد) :
                        // 0 par défaut si aucune valeur n'a été saisie.
                        'montant_condamne' => $request->montants[$partieId] ?? 0,
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