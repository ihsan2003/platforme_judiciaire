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

        // Comparaison réelle à la semaine précédente : avant on affichait "up=true"
        // dès qu'il y avait ≥1 jugement cette semaine (toujours vert dès qu'il y a
        // de l'activité, jamais de flèche rouge, sans aucune comparaison).
        $jugementsSemainePrecedente = Jugement::whereBetween('date_jugement', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek(),
            ])->count();

        $upJugements = $jugementsCetteSemaine > $jugementsSemainePrecedente
            ? true
            : ($jugementsCetteSemaine < $jugementsSemainePrecedente ? false : null);

        // ─── Comparaisons mois actuel / mois précédent pour les autres cartes ──
        // Même principe que $dossiersMoisPrecedent / $croissanceTotal ci-dessus,
        // appliqué à "actifs", "en_cours" (réexamen) et "exécutions" pour que
        // toutes les cartes aient une vraie flèche de tendance, comme la carte "jugés".
        $moisPrecedentAnnee = now()->subMonthNoOverflow()->year;
        $moisPrecedentMois  = now()->subMonthNoOverflow()->month;

        $actifsCeMois = DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsActifs))
            ->whereYear('date_ouverture', now()->year)
            ->whereMonth('date_ouverture', now()->month)
            ->count();
        $actifsMoisPrecedent = DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsActifs))
            ->whereYear('date_ouverture', $moisPrecedentAnnee)
            ->whereMonth('date_ouverture', $moisPrecedentMois)
            ->count();
        $upActifs = $actifsCeMois > $actifsMoisPrecedent
            ? true
            : ($actifsCeMois < $actifsMoisPrecedent ? false : null);

        $enCoursCeMois = DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsEnReexamen))
            ->whereYear('date_ouverture', now()->year)
            ->whereMonth('date_ouverture', now()->month)
            ->count();
        $enCoursMoisPrecedent = DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsEnReexamen))
            ->whereYear('date_ouverture', $moisPrecedentAnnee)
            ->whereMonth('date_ouverture', $moisPrecedentMois)
            ->count();
        $upEnCours = $enCoursCeMois > $enCoursMoisPrecedent
            ? true
            : ($enCoursCeMois < $enCoursMoisPrecedent ? false : null);

        $executionsCeMois = Execution::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $executionsMoisPrecedent = Execution::whereYear('created_at', $moisPrecedentAnnee)
            ->whereMonth('created_at', $moisPrecedentMois)
            ->count();
        $upExecutions = $executionsCeMois > $executionsMoisPrecedent
            ? true
            : ($executionsCeMois < $executionsMoisPrecedent ? false : null);

        $dossiers = [
            'total'              => DossierJudiciaire::count(),
            'actifs'             => DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsActifs))->count(),
            'en_cours'           => DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsEnReexamen))->count(),
            'juges'              => DossierJudiciaire::whereHas('statut', fn ($q) => $q->whereIn('statut_dossier', $statutsJuges))->count(),
            'executions'         => Execution::count(),
            'ce_mois'            => $dossiersMoisActuel,
            'croissance_pct'     => $croissanceTotal,
            'actifs_ce_mois'     => $actifsCeMois,
            'up_actifs'          => $upActifs,
            'en_cours_ce_mois'   => $enCoursCeMois,
            'up_en_cours'        => $upEnCours,
            'jugements_semaine'  => $jugementsCetteSemaine,
            'up_jugements'       => $upJugements,
            'executions_ce_mois' => $executionsCeMois,
            'up_executions'      => $upExecutions,
        ];

        // ─── RÉCLAMATIONS ────────────────────────────────────────────
        // Idem, un seul groupBy au lieu de 4 requêtes whereHas répétées
        $statsReclamations = Reclamation::query()
            ->join('statut_reclamations', 'reclamations.id_statut_reclamation', '=', 'statut_reclamations.id')
            ->selectRaw('statut_reclamations.statut_reclamation, COUNT(*) as total')
            ->groupBy('statut_reclamations.statut_reclamation')
            ->pluck('total', 'statut_reclamation');

        // Les vrais statuts (seeder) sont en arabe : 'قيد المعالجة', 'تمت المعالجة',
        // 'مغلقة' — le code cherchait des libellés français ('En cours', 'Reçue',
        // 'Clôturée') qui n'existent dans aucune ligne de statut_reclamations,
        // donc ces compteurs (dont l'alerte "شكايات قيد المعالجة") tombaient
        // toujours à 0.
        $reclamationsEnCoursActuel = $statsReclamations->get('قيد المعالجة', 0);

        // Le trend affichait le nombre de réclamations "en cours" (un statut,
        // pas une évolution). Ici on compare le total des réclamations reçues
        // ce mois-ci au total reçu le mois précédent (basé sur date_reception),
        // exactement comme pour la carte "إجمالي الملفات".
        $reclamationsMoisActuel = Reclamation::whereYear('date_reception', now()->year)
            ->whereMonth('date_reception', now()->month)
            ->count();

        $reclamationsMoisPrecedent = Reclamation::whereYear('date_reception', $moisPrecedentAnnee)
            ->whereMonth('date_reception', $moisPrecedentMois)
            ->count();

        $croissanceReclamations = $reclamationsMoisPrecedent > 0
            ? round((($reclamationsMoisActuel - $reclamationsMoisPrecedent) / $reclamationsMoisPrecedent) * 100, 1)
            : ($reclamationsMoisActuel > 0 ? 100.0 : 0.0);

        // Pour les réclamations, une baisse est une bonne nouvelle (vert) et une
        // hausse une mauvaise nouvelle (rouge) — inverse du sens habituel. On
        // garde toutefois une flèche qui reflète le sens réel du nombre (↑ si
        // ça augmente, ↓ si ça diminue), seule la couleur est inversée.
        $arrowReclamations = $croissanceReclamations > 0
            ? true
            : ($croissanceReclamations < 0 ? false : null);

        $upReclamations = $croissanceReclamations < 0
            ? true
            : ($croissanceReclamations > 0 ? false : null);

        $reclamations = [
            'total'          => Reclamation::count(),
            'traitees'       => $statsReclamations->get('تمت المعالجة', 0),
            'en_cours'       => $reclamationsEnCoursActuel,
            'cloturees'      => $statsReclamations->get('مغلقة', 0),
            'ce_mois'        => $reclamationsMoisActuel,
            'croissance_pct' => $croissanceReclamations,
            'up_pct'         => $upReclamations,
            'arrow_pct'      => $arrowReclamations,
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
            'reclamations_en_cours'    => $reclamations['en_cours'],
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
        
        // مع = pour, ضد = contre
        $pour    = (int) ($positionStats->get('مع')->total ?? 0);
        $contre  = (int) ($positionStats->get('ضد')->total ?? 0);
        
        $totalResultats = $pour + $contre;
        
        $resultatsJugements = [
            'pour'            => $pour,
            'contre'          => $contre,
            'total'           => $totalResultats,
            'pct_pour'        => $totalResultats > 0 ? round($pour / $totalResultats * 100, 1) : 0,
            'pct_contre'      => $totalResultats > 0 ? round($contre / $totalResultats * 100, 1) : 0,
        ];
        
        // ─── 3. MONTANTS FINANCIERS PAR POSITION (POUR / CONTRE / PARTIEL) ───────────
        // On ne prend en compte, par dossier, que le DERNIER jugement valide —
        // même règle que DossierJudiciaire::jugementValid / financeValide (plus
        // haut degré de juridiction, puis date la plus récente) — au lieu de
        // sommer tous les jugements/finances du dossier : un dossier peut avoir
        // plusieurs jugements (appel, cassation, réexamen...) et seul le
        // dernier montant jugé doit compter dans la khilasa financière.

        $positionsInstitutionFinance = \App\Models\PositionInstitution::pluck('position', 'id');

        $dossiersAvecJugements = DossierJudiciaire::query()
            ->whereHas('dossierTribunaux.jugements.finance', fn ($q) => $q->whereNotNull('montant_condamne'))
            ->with([
                'dossierTribunaux.degre',
                'dossierTribunaux.jugements.finance',
                'dossierTribunaux.jugements.parties',
            ])
            ->get();

        $montantTotal   = 0.0;
        $montantPaye    = 0.0;
        $montantPour    = 0.0;
        $montantContre  = 0.0;
        $montantPartiel = 0.0;
        $nbDossiersFin  = 0;
        $mensuel        = [];

        foreach ($dossiersAvecJugements as $dossierFin) {
            $jugementValide = $dossierFin->jugementValid;
            $finance        = $jugementValide?->finance;

            if (! $finance || $finance->montant_condamne === null) {
                continue; // le dernier jugement valide n'a pas (encore) de finance chiffrée
            }

            $montantTotal += (float) $finance->montant_condamne;
            $montantPaye  += (float) ($finance->montant_paye ?? 0);
            $nbDossiersFin++;

            if ($jugementValide->date_jugement) {
                $mois = $jugementValide->date_jugement->format('Y-m');
                $mensuel[$mois] = ($mensuel[$mois] ?? 0) + (float) $finance->montant_condamne;
            }

            // On répartit le MONTANT TOTAL de la finance (toujours renseigné)
            // dans le panier pour/contre selon la position déclarée de
            // l'établissement — et non le montant individuel de sa propre ligne
            // dans jugement_parties, qui reste souvent vide (le montant est en
            // général saisi sur la ligne de la partie adverse, pas sur la
            // ligne de l'établissement), ce qui faisait toujours ressortir 0.
            $partieEtab = $jugementValide->parties->first(fn ($p) => $p->est_entraide);
            $position   = $partieEtab
                ? $positionsInstitutionFinance->get($partieEtab->pivot->id_position_institution)
                : null;

            match ($position) {
                'مع'    => $montantPour    += (float) $finance->montant_condamne,
                'ضد'    => $montantContre  += (float) $finance->montant_condamne,
                'جزئي'  => $montantPartiel += (float) $finance->montant_condamne,
                default => null,
            };
        }

        $montantRestant = max(0, $montantTotal - $montantPaye);

        $statsFinancesGraphe = [
            'montant_total'   => $montantTotal,
            'montant_pour'    => $montantPour,
            'montant_contre'  => $montantContre,
            'montant_paye'    => $montantPaye,
            'montant_restant' => $montantRestant,
            'nb_dossiers'     => $nbDossiersFin,
            // Ventilation mensuelle des condamnations (12 derniers mois), basée
            // uniquement sur le dernier jugement valide de chaque dossier.
            'mensuel'         => collect($mensuel),
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