<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Audience;
use App\Models\DossierJudiciaire;
use App\Models\DossierTribunal;
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
        // Utilise les scopes du modèle + un seul count par statut via DB groupBy
        $statsDossiers = DossierJudiciaire::query()
            ->join('statut_dossiers', 'dossier_judiciaires.id_statut_dossier', '=', 'statut_dossiers.id')
            ->selectRaw('statut_dossiers.statut_dossier, COUNT(*) as total')
            ->groupBy('statut_dossiers.statut_dossier')
            ->pluck('total', 'statut_dossier');

        $dossiers = [
            'total'     => DossierJudiciaire::count(),
            'actifs'    => DossierJudiciaire::actifs()->count(),
            'en_cours'  => $statsDossiers->get('En cours', 0),
            'juges'     => $statsDossiers->get('Jugé', 0),
            'executes'  => $statsDossiers->get('Exécuté', 0),
            'ce_mois'   => DossierJudiciaire::whereMonth('date_ouverture', now()->month)->count(),
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
        
        // ─── 2. RÉSULTATS POUR / CONTRE L'ÉTABLISSEMENT ──────────────────────────────
        // L'établissement est identifié par Partie.est_entraide = true
        // On passe par jugement_parties → parties.est_entraide
        // puis on compte les jugements où l'établissement est demandeur (المدعي)
        // vs défendeur (المدعى عليه) selon le type de partie dans le dossier
        //
        // Logique retenue (plus fiable car position_institution peut être NULL) :
        //   - On regarde si l'établissement figure comme المدعي (demandeur) dans le dossier
        //   - Si oui et jugement favorable → POUR ; sinon → CONTRE
        //   - "favorable" = déterminé par est_definitif + qui est condamné dans jugement_parties
        //
        // Approche simplifiée et robuste : on cherche dans les finances
        //   - Pour l'établissement : jugements où une partie est_entraide ET n'est PAS dans jugement_parties
        //     (l'établissement n'est pas la partie condamnée)
        //   - Contre : jugements où une partie est_entraide EST dans jugement_parties avec montant_condamne > 0
        
        $jugementsPourContre = \App\Models\Jugement::query()
            ->whereHas('dossierTribunal.dossier.dossierParties.partie', fn($q) => $q->where('est_entraide', true))
            ->with([
                'parties:id,nom_partie,est_entraide',
                'finance:id,id_jugement,montant_condamne,montant_paye',
                'dossierTribunal.dossier.dossierParties.partie:id,est_entraide,nom_partie',
                'dossierTribunal.dossier.dossierParties.typePartie:id,type_partie',
            ])
            ->get();
        
        $pour   = 0;
        $contre = 0;
        
        foreach ($jugementsPourContre as $jugement) {
            // L'établissement est-il parmi les parties CONDAMNÉES dans ce jugement ?
            $etablissementCondamne = $jugement->parties
                ->where('est_entraide', true)
                ->isNotEmpty();
        
            if ($etablissementCondamne) {
                $contre++;
            } else {
                $pour++;
            }
        }
        
        $totalResultats = $pour + $contre;
        
        $resultatsJugements = [
            'pour'            => $pour,
            'contre'          => $contre,
            'total'           => $totalResultats,
            'pct_pour'        => $totalResultats > 0 ? round($pour / $totalResultats * 100, 1) : 0,
            'pct_contre'      => $totalResultats > 0 ? round($contre / $totalResultats * 100, 1) : 0,
        ];
        
        // ─── 3. MONTANTS FINANCIERS (POUR / CONTRE L'ÉTABLISSEMENT) ──────────────────
        // On s'appuie sur la table finances reliée aux jugements
        // Pour chaque jugement :
        //   - Si l'établissement est condamné (dans jugement_parties) → montant CONTRE
        //   - Sinon → montant POUR (l'établissement est le bénéficiaire de la condamnation)
        
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
        
        // Montants ventilés pour/contre via jugement_parties + est_entraide
        $montantContre = \App\Models\Finance::query()
            ->join('jugements', 'finances.id_jugement', '=', 'jugements.id')
            ->join('jugement_parties', 'jugements.id', '=', 'jugement_parties.id_jugement')
            ->join('parties', 'jugement_parties.id_partie', '=', 'parties.id')
            ->where('parties.est_entraide', true)
            ->whereNotNull('jugement_parties.montant_condamne')
            ->sum('jugement_parties.montant_condamne');
        
        $montantTotal = (float) ($statsFinances->total_condamne ?? 0);
        $montantContre = (float) $montantContre;
        $montantPour   = max(0, $montantTotal - $montantContre);
        $montantPaye   = (float) ($statsFinances->total_paye ?? 0);
        $montantRestant = max(0, $montantTotal - $montantPaye);
        
        $statsFinancesGraphe = [
            'montant_total'   => $montantTotal,
            'montant_pour'    => $montantPour,
            'montant_contre'  => $montantContre,
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
}
