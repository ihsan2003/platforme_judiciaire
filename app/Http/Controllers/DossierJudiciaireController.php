<?php

namespace App\Http\Controllers;

use App\Models\DossierJudiciaire;
use App\Models\DossierPartie;
use App\Models\DossierTribunal;
use App\Models\TypeAffaire;
use App\Models\StatutDossier;
use App\Models\Tribunal;
use App\Models\TypeTribunal;
use App\Models\TypePartie;
use App\Models\Avocat;
use App\Models\Partie;
use App\Models\DegreeJuridiction;
use App\Http\Requests\Dossiers\StoreDossierRequest;
use App\Http\Requests\Dossiers\UpdateDossierRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DossierJudiciaireController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(DossierJudiciaire::class, 'dossier');
    }

    // ─────────────────────────────────────────
    // INDEX : liste paginée avec filtres
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $dossiers = DossierJudiciaire::query()
            ->with(['typeAffaire', 'statut', 'createdBy', 'dossierTribunaux.tribunal'])
            ->when($request->type,   fn($q, $v) => $q->parType($v))
            ->when($request->statut, fn($q, $v) => $q->whereHas(
                'statutDossier', fn($q) => $q->where('id', $v)
            ))
            ->when($request->search, fn($q, $v) => $q->where(
                fn($q) => $q->where('numero_dossier_interne', 'like', "%{$v}%")
                             ->orWhere('numero_dossier_tribunal', 'like', "%{$v}%")
            ))
            ->when($request->date_debut, fn($q, $v) => $q->where('date_ouverture', '>=', $v))
            ->when($request->date_fin,   fn($q, $v) => $q->where('date_ouverture', '<=', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $typesAffaire   = TypeAffaire::orderBy('affaire')->get();
        $statutDossiers = StatutDossier::orderBy('statut_dossier')->get();

        // Stats rapides pour le tableau de bord en-tête
        $stats = [
            'total'   => DossierJudiciaire::count(),
            'actifs'  => DossierJudiciaire::actifs()->count(),
            'ce_mois' => DossierJudiciaire::whereMonth('created_at', now()->month)->count(),
        ];

        return view('dossiers.index', compact('dossiers', 'typesAffaire', 'statutDossiers', 'stats'));
    }

    // ─────────────────────────────────────────
    // CREATE : formulaire de création complet
    // ─────────────────────────────────────────
    public function create()
    {
        $typesAffaire       = TypeAffaire::orderBy('affaire')->get();
        $statutDossiers     = StatutDossier::orderBy('statut_dossier')->get();
        $tribunaux          = Tribunal::with('typeTribunal')->orderBy('nom_tribunal')->get();
        $typesPartie        = TypePartie::orderBy('type_partie')->get();
        $avocats            = Avocat::orderBy('nom_avocat')->get();
        $degresJuridiction  = DegreeJuridiction::orderBy('degre_juridiction')->get();

        return view('dossiers.create', compact(
            'typesAffaire', 'statutDossiers',
            'tribunaux', 'typesPartie', 'avocats', 'degresJuridiction'
        ));
    }

    // ─────────────────────────────────────────
    // STORE : enregistrement avec transaction
    // ─────────────────────────────────────────
    public function store(StoreDossierRequest $request)
    {
        $dossier = DB::transaction(function () use ($request) {

            $dossier = DossierJudiciaire::create([
                ...$request->safe()->only([
                    'numero_dossier_interne',
                    'numero_dossier_tribunal',
                    'id_type_affaire',
                    'id_statut_dossier',
                    'date_ouverture',
                    'date_cloture',
                ]),
                'created_by' => Auth::id(),
            ]);

            // Lier le tribunal si sélectionné
            if ($request->filled('id_tribunal')) {
                DossierTribunal::create([
                    'id_dossier'  => $dossier->id,
                    'id_tribunal' => $request->id_tribunal,
                    'id_degre'    => $request->id_degre,
                    'date_debut'  => $request->date_debut_tribunal ?? $request->date_ouverture,
                    'date_fin'    => $request->date_fin_tribunal,
                ]);
            }

            return $dossier;
        });

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('success', "Dossier « {$dossier->numero_dossier_interne} » créé avec succès.");
    }

    // ─────────────────────────────────────────
    // SHOW : vue détail complète avec onglets
    // ─────────────────────────────────────────
    public function show(DossierJudiciaire $dossier)
    {
        $dossier->load([
            'typeAffaire',
            'statut',
            'createdBy',
            'dossierTribunaux.tribunal.typeTribunal',
            'dossierTribunaux.degre',
            'dossierTribunaux.audiences.typeAudience',
            'dossierTribunaux.jugements.recours',
            'documents.typeDocument',
        ]);

        // Parties avec toutes leurs infos de liaison
        $dossierParties = DossierPartie::with(['partie', 'typePartie', 'avocat'])
            ->where('id_dossier', $dossier->id)
            ->get();

        // Données pour les formulaires d'ajout inline
        $tribunaux         = Tribunal::with('typeTribunal')->orderBy('nom_tribunal')->get();
        $typesPartie       = TypePartie::orderBy('type_partie')->get();
        $avocats           = Avocat::orderBy('nom_avocat')->get();
        $degresJuridiction = DegreeJuridiction::orderBy('degre_juridiction')->get();
        $parties           = Partie::orderBy('nom_partie')->get(); // pour la recherche

        return view('dossiers.show', compact(
            'dossier', 'dossierParties',
            'tribunaux', 'typesPartie', 'avocats', 'degresJuridiction', 'parties'
        ));
    }

    // ─────────────────────────────────────────
    // EDIT : formulaire d'édition
    // ─────────────────────────────────────────
    public function edit(DossierJudiciaire $dossier)
    {
        $typesAffaire   = TypeAffaire::orderBy('affaire')->get();
        $statutDossiers = StatutDossier::orderBy('statut_dossier')->get();

        return view('dossiers.edit', compact('dossier', 'typesAffaire', 'statutDossiers'));
    }

    // ─────────────────────────────────────────
    // UPDATE : mise à jour
    // ─────────────────────────────────────────
    public function update(UpdateDossierRequest $request, DossierJudiciaire $dossier)
    {
        DB::transaction(fn() => $dossier->update($request->validated()));

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('success', 'Dossier mis à jour avec succès.');
    }

    // ─────────────────────────────────────────
    // DESTROY : suppression douce
    // ─────────────────────────────────────────
    public function destroy(DossierJudiciaire $dossier)
    {
        $numero = $dossier->numero_dossier_interne;
        $dossier->delete();

        return redirect()
            ->route('dossiers.index')
            ->with('success', "Dossier « {$numero} » archivé.");
    }
}