<?php
// app/Services/DashboardStatsService.php

namespace App\Services;

use App\Models\Audience;
use App\Models\DossierJudiciaire;
use App\Models\Finance;
use App\Models\JugementPartie;
use App\Models\Reclamation;

/**
 * Calcule l'ensemble des indicateurs affichés sur le tableau de bord
 * (dashboard.index) et réutilisés tels quels par le formulaire de
 * rapports (rapports.index), afin d'éviter de dupliquer ces grosses
 * requêtes dans plusieurs contrôleurs.
 */
class DashboardStatsService
{
    public function __construct(
        private readonly AlertesService $alertesService,
    ) {
    }

    /**
     * Retourne un tableau associatif contenant toutes les clés attendues
     * par la vue : dossiers, reclamations, alertes, audiencesAVenir,
     * derniersDossiers, evolutionMois, dossiersParAffaire,
     * resultatsJugements, statsFinancesGraphe.
     */
    public function genererDonnees(): array
    {
        // ─── DOSSIERS ───────────────────────────────────────────────
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

        $alertes = $this->alertesService->genererAlertes();

        // ─── DERNIERS DOSSIERS ────────────────────────────────────────
        $derniersDossiers = DossierJudiciaire::with(['typeAffaire', 'statut', 'dossierTribunaux.tribunal'])
            ->latest()
            ->limit(5)
            ->get();

        // ─── ÉVOLUTION MENSUELLE (12 derniers mois) ───────────────────────
        $evolutionRaw = DossierJudiciaire::query()
            ->selectRaw("DATE_FORMAT(date_ouverture, '%Y-%m') as mois, COUNT(*) as total")
            ->where('date_ouverture', '>=', now()->subMonths(11)->startOfMonth())
            ->whereNotNull('date_ouverture')
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

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

        // ─── 1. DOSSIERS PAR TYPE D'AFFAIRE ──────────────────────────────
        $statsDossierParAffaire = DossierJudiciaire::query()
            ->join('type_affaires', 'dossier_judiciaires.id_type_affaire', '=', 'type_affaires.id')
            ->selectRaw('type_affaires.affaire, COUNT(*) as total')
            ->groupBy('type_affaires.affaire')
            ->orderByDesc('total')
            ->get();

        $totalDossiersAffaire = $statsDossierParAffaire->sum('total');

        $dossiersParAffaire = [
            'labels'      => $statsDossierParAffaire->pluck('affaire')->toArray(),
            'values'      => $statsDossierParAffaire->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
            'pourcentages'=> $statsDossierParAffaire->map(fn ($r) =>
                $totalDossiersAffaire > 0
                    ? round($r->total / $totalDossiersAffaire * 100, 1)
                    : 0
            )->toArray(),
            'total'       => $totalDossiersAffaire,
        ];

        // ─── 2. RÉSULTATS POUR / CONTRE / PARTIEL DE L'ÉTABLISSEMENT ─────
        // Source de vérité : jugement_parties.id_position_institution,
        // renseignée uniquement sur la ligne de la partie de l'établissement
        // (parties.est_entraide = true) — voir JugementController::store().
        $positionStats = JugementPartie::query()
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
            'pour'        => $pour,
            'contre'      => $contre,
            'partiel'     => $partiel,
            'total'       => $totalResultats,
            'pct_pour'    => $totalResultats > 0 ? round($pour / $totalResultats * 100, 1) : 0,
            'pct_contre'  => $totalResultats > 0 ? round($contre / $totalResultats * 100, 1) : 0,
            'pct_partiel' => $totalResultats > 0 ? round($partiel / $totalResultats * 100, 1) : 0,
        ];

        // ─── 3. MONTANTS FINANCIERS PAR POSITION ─────────────────────────
        $statsFinances = Finance::query()
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
            'mensuel'         => Finance::query()
                ->join('jugements', 'finances.id_jugement', '=', 'jugements.id')
                ->selectRaw("DATE_FORMAT(jugements.date_jugement, '%Y-%m') as mois, SUM(finances.montant_condamne) as total")
                ->where('jugements.date_jugement', '>=', now()->subMonths(11)->startOfMonth())
                ->whereNotNull('finances.montant_condamne')
                ->groupBy('mois')
                ->orderBy('mois')
                ->pluck('total', 'mois'),
        ];

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

        return compact(
            'dossiers',
            'reclamations',
            'alertes',
            'audiencesAVenir',
            'derniersDossiers',
            'evolutionMois',
            'dossiersParAffaire',
            'resultatsJugements',
            'statsFinancesGraphe',
        );
    }
}