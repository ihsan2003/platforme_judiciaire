<?php

namespace App\Http\Controllers;

use App\Models\DossierJudiciaire;
use App\Models\DossierPartie;
use App\Models\DossierTribunal;
use App\Models\TypeAffaire;
use App\Models\StatutDossier;
use App\Models\Tribunal;
use App\Models\TypePartie;
use App\Models\Avocat;
use App\Models\Partie;
use App\Models\DegreeJuridiction;
use App\Models\Jugement;
use App\Models\Region;
use App\Models\TypeDocument;
use App\Http\Requests\Dossiers\StoreDossierRequest;
use App\Http\Requests\Dossiers\UpdateDossierRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DossierJudiciaireController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(DossierJudiciaire::class, 'dossier');
    }
    

    // =========================================================
    // INDEX — Liste paginée avec filtres et stats
    // =========================================================
    public function index(Request $request)
    {
        // On charge uniquement les relations légères nécessaires à la liste,
        // évitant ainsi les jointures lourdes (audiences, jugements, etc.)
        // qui seraient inutiles dans un tableau de bord.
        $dossiers = DossierJudiciaire::query()
            ->with([
                'typeAffaire',
                'statut',
                'createdBy:id,name',                 // seulement id+name, pas tout le modèle User
                'dossierTribunaux.tribunal',          // pour afficher le ou les tribunaux dans la liste
            ])
            ->when($request->type,   fn($q, $v) => $q->parType($v))
            ->when($request->statut, fn($q, $v) => $q->whereHas(
                'statut', fn($q) => $q->where('id', $v)
            ))
            ->when($request->search, fn($q, $v) =>
                $q->where(fn($q) =>
                    $q->where('numero_dossier_interne', 'like', "%{$v}%")
                      ->orWhere('numero_dossier_tribunal', 'like', "%{$v}%")
                )
            )
            ->when($request->date_debut, fn($q, $v) => $q->where('date_ouverture', '>=', $v))
            ->when($request->date_fin,   fn($q, $v) => $q->where('date_ouverture', '<=', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $typesAffaire   = TypeAffaire::orderBy('affaire')->get();
        $statutDossiers = StatutDossier::orderBy('statut_dossier')->get();

        // Un seul appel groupé pour les stats plutôt que 3 COUNT() séparés.
        $stats = [
            'total'   => DossierJudiciaire::count(),
            'actifs'  => DossierJudiciaire::actifs()->count(),
            'ce_mois' => DossierJudiciaire::whereMonth('created_at', now()->month)->count(),
        ];

        return view('dossiers.index', compact('dossiers', 'typesAffaire', 'statutDossiers', 'stats'));
    }

    // =========================================================
    // CREATE — Formulaire de création
    // =========================================================
    public function create()
    {
        $typesAffaire      = TypeAffaire::orderBy('affaire')->get();

        return view('dossiers.create', compact('typesAffaire'));

    }

    // =========================================================
    // STORE — Enregistrement avec transaction
    // =========================================================
    public function store(StoreDossierRequest $request): RedirectResponse
    {
        $dossier = DB::transaction(function () use ($request) {

            // Créer le dossier principal
            $dossier = DossierJudiciaire::create([
                ...$request->safe()->only([
                    'numero_dossier_interne',
                    'numero_dossier_tribunal',
                    'id_type_affaire',
                    'date_ouverture',
                    'date_cloture',
                ]),
                'created_by' => Auth::id(),
            ]);

            // Lier le tribunal initial si fourni dans le formulaire.
            // On vérifie la présence de id_tribunal car le champ est optionnel.
            if ($request->filled('id_tribunal')) {
                DossierTribunal::create([
                    'id_dossier'  => $dossier->id,
                    'id_tribunal' => $request->id_tribunal,
                    'id_degre'    => $request->id_degre,
                    // Si aucune date fournie, on utilise la date d'ouverture du dossier
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

    // =========================================================
    // SHOW — Vue détail complète avec tous les onglets
    //
    // C'est ici que l'on fait l'eager loading le plus complet,
    // car l'utilisateur a besoin de voir toutes les relations.
    // On structure le chargement par "blocs" selon les onglets
    // de la vue pour que ce soit lisible et maintenable.
    // =========================================================
    public function show(DossierJudiciaire $dossier)
    {
        $dossier->load([
            // ── Informations générales ──
            'typeAffaire',
            'statut',
            'createdBy:id,name',

            // ── Onglet Parties ──
            // On charge la table pivot dossier_parties avec toutes ses relations
            'dossierTribunaux.tribunal.typeTribunal',
            'dossierTribunaux.tribunal.province.region', // pour afficher la géographie complète

            // ── Onglet Tribunaux ──
            // Le degré de juridiction est directement sur dossier_tribunal
            'dossierTribunaux.degre',

            // ── Onglet Audiences (imbriquées dans chaque dossier_tribunal) ──
            // On charge les audiences de chaque instance avec leur type
            'dossierTribunaux.audiences.typeAudience',
            'dossierTribunaux.audiences.juge',

            // ── Onglet Jugements ──
            // Les jugements appartiennent à un dossier_tribunal (une instance)
            // On charge aussi les recours et l'exécution liés à chaque jugement
            'dossierTribunaux.jugements.juge',
            'dossierTribunaux.jugements.recours.typeRecours',
            'dossierTribunaux.jugements.executions.statut',
            'dossierTribunaux.jugements.finance',
            // Les parties liées à chaque jugement (table pivot jugement_parties)
            'dossierTribunaux.jugements.parties.pivot',

            // ── Onglet Documents ──
            'documents.typeDocument',
            'documents.partie',
        ]);

        // Les parties sont chargées séparément via DossierPartie pour avoir
        // accès aux colonnes pivot (id_type_partie, id_avocat, est_institution, date_entree)
        $dossierParties = DossierPartie::with([
            'partie',
            'typePartie',
            'avocat',
        ])
        ->where('id_dossier', $dossier->id)
        ->get();

        // Données pour les formulaires d'ajout inline dans les modals
        $tribunaux         = Tribunal::with('typeTribunal')->orderBy('nom_tribunal')->get();
        $typesPartie       = TypePartie::orderBy('type_partie')->get();
        $avocats           = Avocat::orderBy('nom_avocat')->get();
        $degresJuridiction = DegreeJuridiction::orderBy('degre_juridiction')->get();
        $parties           = Partie::orderBy('nom_partie')->get();

        // Calcul des statistiques rapides pour l'en-tête de la fiche
        $stats = [
            'nb_audiences'  => $dossier->dossierTribunaux->flatMap->audiences->count(),
            'nb_jugements'  => $dossier->dossierTribunaux->flatMap->jugements->count(),
            'nb_parties'    => $dossierParties->count(),
            'nb_tribunaux'  => $dossier->dossierTribunaux->count(),
            'nb_documents'  => $dossier->documents->count(),
            // Prochaine audience à venir parmi toutes les instances
            'prochaine_audience' => $dossier->dossierTribunaux
                ->flatMap->audiences
                ->where('date_audience', '>=', now()->toDateString())
                ->sortBy('date_audience')
                ->first(),
            // Jugement le plus récent pour afficher rapidement l'issue
            'dernier_jugement' => $dossier->dossierTribunaux
                ->flatMap->jugements
                ->sortByDesc('date_jugement')
                ->first(),
            'total_finances' => $dossier->dossierTribunaux
                ->flatMap->jugements
                ->pluck('finance')
                ->filter()
                ->sum('montant_condamne'),

            'total_paye' => $dossier->dossierTribunaux
                ->flatMap->jugements
                ->pluck('finance')
                ->filter()
                ->sum('montant_paye'),
        ];

        $typesDocuments = TypeDocument::all();
        $regions = \App\Models\Region::orderBy('region')->get();


        return view('dossiers.show', compact(
            'dossier',
            'dossierParties',
            'tribunaux',
            'typesPartie',
            'avocats',
            'degresJuridiction',
            'parties',
            'stats',
            'typesDocuments',
            'regions'

        ));
    }

    // =========================================================
    // EDIT — Formulaire de modification (informations de base)
    // Les parties et tribunaux se modifient depuis la fiche via
    // leurs propres contrôleurs (DossierPartieController, etc.)
    // =========================================================
    public function edit(DossierJudiciaire $dossier)
    {
        $typesAffaire   = TypeAffaire::orderBy('affaire')->get();
        $statutDossiers = StatutDossier::orderBy('statut_dossier')->get();

        // On charge uniquement les relations utiles à la page d'édition :
        // les parties et tribunaux sont affichés en lecture seule dans le résumé
        $dossier->load([
            'typeAffaire',
            'statut',
            'createdBy:id,name',
            'dossierTribunaux.tribunal',
            'parties',
        ]);

        return view('dossiers.edit', compact('dossier', 'typesAffaire', 'statutDossiers'));
    }

    // =========================================================
    // UPDATE — Mise à jour des informations de base
    // =========================================================
    public function update(UpdateDossierRequest $request, DossierJudiciaire $dossier): RedirectResponse
    {
        DB::transaction(fn() => $dossier->update($request->validated()));

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('success', 'Dossier mis à jour avec succès.');
    }

    // =========================================================
    // DESTROY — Suppression douce (SoftDeletes)
    // =========================================================
    public function destroy(DossierJudiciaire $dossier): RedirectResponse
    {
        // RG06 : on empêche l'archivage si une exécution est en cours
        $executionEnCours = $dossier->jugements()
            ->whereHas('executions', fn($q) =>
                $q->whereHas('statut', fn($q) =>
                    $q->where('statut_execution', 'En cours')
                )
            )
            ->exists();

        if ($executionEnCours) {
            return redirect()
                ->route('dossiers.show', $dossier)
                ->with('error', 'Impossible d\'archiver ce dossier : une exécution est en cours.');
        }

        $numero = $dossier->numero_dossier_interne;
        $dossier->delete(); // Soft delete — les données restent en BDD

        return redirect()
            ->route('dossiers.index')
            ->with('success', "Dossier « {$numero} » archivé.");
    }

    // =========================================================
    // EXPORT PDF — Génère la fiche complète du dossier en PDF
    // Utilise barryvdh/laravel-dompdf déjà présent dans composer.json
    // =========================================================
    public function exportPdf(DossierJudiciaire $dossier): Response
    {
        $this->authorize('view', $dossier);

        // On réutilise exactement le même eager loading que show()
        // pour que la vue PDF ait accès à toutes les données
        $dossier->load([
            'typeAffaire',
            'statut',
            'createdBy:id,name',
            'dossierTribunaux.tribunal',
            'dossierTribunaux.degre',
            'dossierTribunaux.jugements.juge',
            'dossierTribunaux.jugements.finance',
            'dossierTribunaux.jugements.parties',
            'documents.typeDocument',
            'documents.partie',
        ]);

        // Les parties pivot sont chargées séparément comme dans show()
        $dossier->dossierParties = DossierPartie::with(['partie', 'typePartie', 'avocat'])
            ->where('id_dossier', $dossier->id)
            ->get();

        $pdf = Pdf::loadView('dossiers.pdf', compact('dossier'))
                  ->setPaper('A4', 'portrait');

        return $pdf->download("dossier-{$dossier->numero_dossier_interne}.pdf");
    }
}