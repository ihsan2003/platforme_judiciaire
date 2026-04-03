<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Audience;
use App\Models\DossierJudiciaire;
use App\Models\Jugement;
use App\Models\Reclamation;
use App\Models\StatutDossier;

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

        return view('dashboard.index', compact(
            'dossiers',
            'reclamations',
            'alertes',
            'audiencesAVenir',
            'derniersDossiers'
        ));
    }
}
