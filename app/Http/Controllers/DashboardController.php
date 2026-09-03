<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Audience;
use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
use App\Models\Execution;
use App\Models\Jugement;
use App\Models\Reclamation;
use App\Models\StatutDossier;
use App\Models\Finance;
use App\Models\TypeAffaire;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // ─── DOSSIERS ───────────────────────────────────────────────
        // Regroupement des statuts (statut_dossiers.statut_dossier) par catégorie
        // affichée sur le tableau de bord. Les 3 groupes ci-dessous forment une
        // partition complète des 9 statuts saisis dans StatutDossierSeeder/DataSeeder :
        // النشطة (5) + المحكومة (3) + حفظ (1) = تام. On ne réutilise pas le scope
        // DossierJudiciaire::actifs() (qui exclut uniquement "حفظ" et sert ailleurs
        // — Avocat, JugementController) pour ne pas changer son comportement existant.
        $statutsActifs = [
            'جاري',
            'في طور الاستئناف',
            'في طور النقض',
            'في طور التعرض',
            'في طور إعادة النظر',
        ];
        $statutsEnReexamen = ['في طور إعادة النظر'];               // قيد النظر
        $statutsJuges      = ['تم الحكم', 'تم التنفيذ', 'قيد التنفيذ']; // المحكومة

        $dossiersMoisActuel = DossierJudiciaire::whereYear('date_ouverture', now()->year)
            ->whereMonth('date_ouverture', now()->month)
            ->count();

        $dossiersMoisPrecedent = DossierJudiciaire::whereYear('date_ouverture', now()->subMonthNoOverflow()->year)
            ->whereMonth('date_ouverture', now()->subMonthNoOverflow()->month)
            ->count();

        $croissanceTotal = $dossiersMoisPrecedent > 0
            ? round((($dossiersMoisActuel - $dossiersMoisPrecedent) / $dossiersMoisPrecedent) * 100, 1)
            : ($dossiersMoisActuel > 0 ? 100.0 : 0.0);

        $jugementsCetteSemaine = Jugement::whereBetween('date_jugement', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $dossiers = [
            'total'              => DossierJudiciaire::count(),
            'actifs'             => DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsActifs))->count(),
            'en_cours'           => DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsEnReexamen))->count(),
            'juges'              => DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsJuges))->count(),
            'executions'         => Execution::count(),
            'ce_mois'            => $dossiersMoisActuel,
            'croissance_pct'     => $croissanceTotal,
            'actifs_ce_mois'     => DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsActifs))
                                        ->whereYear('date_ouverture', now()->year)
                                        ->whereMonth('date_ouverture', now()->month)
                                        ->count(),
            'en_cours_ce_mois'   => DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsEnReexamen))
                                        ->whereYear('date_ouverture', now()->year)
                                        ->whereMonth('date_ouverture', now()->month)
                                        ->count(),
            'jugements_semaine'  => $jugementsCetteSemaine,
            'executions_ce_mois' => Execution::whereYear('created_at', now()->year)
                                        ->whereMonth('created_at', now()->month)
                                        ->count(),
        ];

        // ─── RÉCLAMATIONS ────────────────────────────────────────────
        // Idem, un seul groupBy au lieu de 4 requêtes whereHas répétées
        $statsReclamations = Reclamation::query()
            ->join('statut_reclamations', 'reclamations.id_statut_reclamation', '=', 'statut_reclamations.id')
            ->selectRaw('statut_reclamations.statut_reclamation, COUNT(*) as total')
            ->groupBy('statut_reclamations.statut_reclamation')
            ->pluck('total', 'statut_reclamation');

        $reclamations = [
            'total'     => Reclamation::count(),
            'recues'    => $statsReclamations->get('Reçue', 0),
            'en_cours'  => $statsReclamations->get('En cours', 0),
            'cloturees' => $statsReclamations->get('Clôturée', 0),
            'en_attente'=> Reclamation::enAttente()->count(),
        ];

        // ─── ALERTES / AGENDA ─────────────────────────────────────────
        // Audiences à venir dans les 7 prochains jours, chargées avec relations
        $audiencesAVenir = Audience::with([
                'dossierTribunal.dossier',
                'dossierTribunal.tribunal',
                'juge',
                'typeAudience',
            ])
            ->whereBetween('date_audience', [today(), today()->addDays(7)])
            ->orderBy('date_audience')
            ->limit(10)
            ->get();

        $alertes = [
            'audiences_proches'       => $audiencesAVenir->count(),
            'jugements_non_definitifs'=> Jugement::where('est_definitif', false)->count(),
            'reclamations_en_attente' => $reclamations['en_attente'],
        ];

        // ─── DERNIERS DOSSIERS ────────────────────────────────────────
        $derniersDossiers = DossierJudiciaire::with(['typeAffaire', 'statut', 'dossierTribunaux.tribunal'])
            ->latest()
            ->limit(5)
            ->get();

        // ─── ÉVOLUTION MENSUELLE (12 derniers mois) ───────────────────────
        // Requête groupée par mois : beaucoup plus efficace que 12 requêtes
        $evolutionRaw = \App\Models\DossierJudiciaire::query()
            ->selectRaw("DATE_FORMAT(date_ouverture, '%Y-%m') as mois, COUNT(*) as total")
            ->where('date_ouverture', '>=', now()->subMonths(11)->startOfMonth())
            ->whereNotNull('date_ouverture')
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');
        
        // Construire un tableau complet des 12 mois (avec 0 si aucun dossier)
        $evolutionLabels = [];
        $evolutionValues = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $key   = $date->format('Y-m');
            $label = $date->translatedFormat('M Y'); // ex: "Jan 2025"
        
            $evolutionLabels[] = $label;
            $evolutionValues[] = (int) $evolutionRaw->get($key, 0);
        }
        
        $evolutionMois = [
            'labels' => $evolutionLabels,
            'values' => $evolutionValues,
        ];

        // ─── 1. DOSSIERS PAR TYPE D'AFFAIRE ──────────────────────────────────────────
        // Une seule requête groupée — on récupère le libellé + le count
        $statsDossierParAffaire = \App\Models\DossierJudiciaire::query()
            ->join('type_affaires', 'dossier_judiciaires.id_type_affaire', '=', 'type_affaires.id')
            ->selectRaw('type_affaires.affaire, COUNT(*) as total')
            ->groupBy('type_affaires.affaire')
            ->orderByDesc('total')
            ->get();
        
        $totalDossiersAffaire = $statsDossierParAffaire->sum('total');
        
        $dossiersParAffaire = [
            'labels'      => $statsDossierParAffaire->pluck('affaire')->toArray(),
            'values'      => $statsDossierParAffaire->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'pourcentages'=> $statsDossierParAffaire->map(fn($r) =>
                $totalDossiersAffaire > 0
                    ? round($r->total / $totalDossiersAffaire * 100, 1)
                    : 0
            )->toArray(),
            'total'       => $totalDossiersAffaire,
        ];
        
        // ─── 2. RÉSULTATS POUR / CONTRE / PARTIEL DE L'ÉTABLISSEMENT ─────────────────
        // On utilise directement la donnée saisie dans jugement_parties.id_position_institution
        // (مع / ضد / جزئي), renseignée UNIQUEMENT sur la ligne de la partie de
        // l'établissement (parties.est_entraide = true) — voir JugementController::store().
        // C'est la source de vérité : plus fiable qu'une déduction, et couvre le "جزئي".
        
        $positionStats = \App\Models\JugementPartie::query()
            ->join('parties', 'jugement_parties.id_partie', '=', 'parties.id')
            ->join('position_institutions', 'jugement_parties.id_position_institution', '=', 'position_institutions.id')
            ->where('parties.est_entraide', true)
            ->selectRaw('position_institutions.position, COUNT(*) as total, SUM(jugement_parties.montant_condamne) as montant')
            ->groupBy('position_institutions.position')
            ->get()
            ->keyBy('position');
        
        // مع = pour, ضد = contre, جزئي = partiel
        $pour    = (int) ($positionStats->get('مع')->total ?? 0);
        $contre  = (int) ($positionStats->get('ضد')->total ?? 0);
        $partiel = (int) ($positionStats->get('جزئي')->total ?? 0);
        
        $totalResultats = $pour + $contre + $partiel;
        
        $resultatsJugements = [
            'pour'            => $pour,
            'contre'          => $contre,
            'partiel'         => $partiel,
            'total'           => $totalResultats,
            'pct_pour'        => $totalResultats > 0 ? round($pour / $totalResultats * 100, 1) : 0,
            'pct_contre'      => $totalResultats > 0 ? round($contre / $totalResultats * 100, 1) : 0,
            'pct_partiel'     => $totalResultats > 0 ? round($partiel / $totalResultats * 100, 1) : 0,
        ];
        
        // ─── 3. MONTANTS FINANCIERS PAR POSITION (POUR / CONTRE / PARTIEL) ───────────
        // Même logique que ci-dessus, mais sur le montant_condamne de la ligne
        // jugement_parties de l'établissement (et non plus une simple soustraction).
        
        $statsFinances = \App\Models\Finance::query()
            ->join('jugements', 'finances.id_jugement', '=', 'jugements.id')
            ->join('dossier_tribunaux', 'jugements.id_dossier_tribunal', '=', 'dossier_tribunaux.id')
            ->join('dossier_judiciaires', 'dossier_tribunaux.id_dossier', '=', 'dossier_judiciaires.id')
            ->selectRaw('
                SUM(finances.montant_condamne) as total_condamne,
                SUM(finances.montant_paye)     as total_paye,
                COUNT(finances.id)             as nb_dossiers
            ')
            ->whereNotNull('finances.montant_condamne')
            ->first();
        
        $montantPour    = (float) ($positionStats->get('مع')->montant ?? 0);
        $montantContre  = (float) ($positionStats->get('ضد')->montant ?? 0);
        $montantPartiel = (float) ($positionStats->get('جزئي')->montant ?? 0);
        
        $montantTotal   = (float) ($statsFinances->total_condamne ?? 0);
        $montantPaye    = (float) ($statsFinances->total_paye ?? 0);
        $montantRestant = max(0, $montantTotal - $montantPaye);
        
        $statsFinancesGraphe = [
            'montant_total'   => $montantTotal,
            'montant_pour'    => $montantPour,
            'montant_contre'  => $montantContre,
            'montant_partiel' => $montantPartiel,
            'montant_paye'    => $montantPaye,
            'montant_restant' => $montantRestant,
            'nb_dossiers'     => (int) ($statsFinances->nb_dossiers ?? 0),
            // Ventilation mensuelle des condamnations (12 derniers mois)
            'mensuel'         => \App\Models\Finance::query()
                ->join('jugements', 'finances.id_jugement', '=', 'jugements.id')
                ->selectRaw("DATE_FORMAT(jugements.date_jugement, '%Y-%m') as mois, SUM(finances.montant_condamne) as total")
                ->where('jugements.date_jugement', '>=', now()->subMonths(11)->startOfMonth())
                ->whereNotNull('finances.montant_condamne')
                ->groupBy('mois')
                ->orderBy('mois')
                ->pluck('total', 'mois'),
        ];
        
        // Construire les labels/values pour le graphe mensuel
        $financesMensuelLabels = [];
        $financesMensuelValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $key   = $date->format('Y-m');
            $financesMensuelLabels[] = $date->translatedFormat('M Y');
            $financesMensuelValues[] = round((float) $statsFinancesGraphe['mensuel']->get($key, 0), 2);
        }
        $statsFinancesGraphe['mensuel_labels'] = $financesMensuelLabels;
        $statsFinancesGraphe['mensuel_values'] = $financesMensuelValues;
        unset($statsFinancesGraphe['mensuel']); // on garde seulement les arrays sérialisables

        return view('dashboard.index', compact(
            'dossiers',
            'reclamations',
            'alertes',
            'audiencesAVenir',
            'derniersDossiers',
            'evolutionMois',
            'dossiersParAffaire',     // ← nouveau
            'resultatsJugements',     // ← nouveau
            'statsFinancesGraphe',
        ));

    }

    public function dossiersParRegion(): JsonResponse
    {
        // On part de la table 'regions' pour être sûr de toutes les avoir
        $data = DB::table('regions')
            ->leftJoin('provinces', 'regions.id', '=', 'provinces.id_region')
            ->leftJoin('tribunaux', 'provinces.id', '=', 'tribunaux.id_province')
            // Jointure cascade pour compter les dossiers
            ->leftJoin('dossier_tribunaux', 'tribunaux.id', '=', 'dossier_tribunaux.id_tribunal')
            ->leftJoin('dossier_judiciaires', 'dossier_tribunaux.id_dossier', '=', 'dossier_judiciaires.id')
            ->select(
                'regions.id',
                'regions.region as nom_region',
                DB::raw('COUNT(DISTINCT dossier_judiciaires.id) as total_dossiers'),
                DB::raw('COUNT(DISTINCT tribunaux.id) as total_tribunaux')
            )
            ->groupBy('regions.id', 'regions.region')
            ->orderBy('regions.id')
            ->get();

        return response()->json($data);
    }
}