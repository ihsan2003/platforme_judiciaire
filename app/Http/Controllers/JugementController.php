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

            ->when(request('position') === 'contre', function ($q) {
                $q->whereHas('parties', function ($query) {
                    $query->where('est_entraide', true)
                        ->where('jugement_parties.montant_condamne', '>', 0);
                });
            })

            ->when(request('position') === 'pour', function ($q) {
                $q->whereHas('parties', function ($query) {
                    $query->where('est_entraide', true)
                        ->where('jugement_parties.montant_condamne', '<=', 0);
                });
            })

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

        // IMPORTANT
        $degres = DegreeJuridiction::orderBy('degre_juridiction')->get();

        return view('jugements.index', compact(
            'jugements',
            'stats',
            'juges',
            'degres'
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
            "Impossible de créer un jugement : le rôle \"{$manquants}\" est manquant."
        );
    }

    // ── RG03 : audience الحكم obligatoire ─────────────────────
    if ($dossierTribunal->audiences->isEmpty()) {
        return back()->with('error',
            'Impossible de créer un jugement : ce dossier/tribunal ne possède aucune audience.'
        );
    }

    $audienceHoukm = $dossierTribunal->audienceHoukm();
    if (! $audienceHoukm) {
        return back()->with('error',
            'Impossible de créer un jugement : aucune audience de type "الحكم" n\'a été enregistrée.'
        );
    }

    // ── RG03 : un seul jugement par instance ──────────────────
    if ($dossierTribunal->jugements->isNotEmpty()) {
        return back()->with('error',
            'Un jugement existe déjà pour cette instance judiciaire.'
        );
    }

    // ── RG02 : date = date audience الحكم ─────────────────────
    $dateAttendue = $audienceHoukm->date_audience->toDateString();
    if ($request->date_jugement !== $dateAttendue) {
        return back()->withErrors([
            'date_jugement' =>
                "La date du jugement doit correspondre à la date de l'audience \"الحكم\" ({$audienceHoukm->date_audience->format('d/m/Y')}).",
        ])->withInput();
    }

    if ($audienceHoukm->date_audience->isFuture()) {
        return back()->withErrors([
            'date_jugement' => "La date du jugement ne peut pas être dans le futur.",
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
            $posLabel   = strtolower($position?->position ?? '');
            $estContre  = str_contains($posLabel, 'contre') || str_contains($posLabel, 'partiel');

            // Ventilation des montants
            $montantEtab    = $etabId ? (float)($request->montants[$etabId] ?? 0) : 0;
            $montantAdverse = $montants
                ->filter(fn($v, $k) => (string)$k !== (string)$etabId)
                ->sum();

            \App\Models\Finance::create([
                'id_jugement'               => $jugement->id,
                'montant_reclame_demandeur' => $estContre ? null       : $montantAdverse,
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