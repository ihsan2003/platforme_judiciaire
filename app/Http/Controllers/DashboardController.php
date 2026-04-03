<?php

namespace App\Http\Controllers;

use App\Models\DossierJudiciaire;
use App\Models\Reclamation;
use App\Models\StatutDossier;
use App\Models\StatutReclamation;
use App\Models\Audience;
use App\Models\Jugement;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()

    {
      

        // 📊 DOSSIERS
        $totalDossiers = DossierJudiciaire::count();

        $dossiersEnCours = DossierJudiciaire::whereHas('statut', function ($q) {
            $q->where('statut_dossier', 'En cours');
        })->count();

        $dossiersJuges = DossierJudiciaire::whereHas('statut', function ($q) {
            $q->where('statut_dossier', 'Jugé');
        })->count();

        $dossiersExecutes = DossierJudiciaire::whereHas('statut', function ($q) {
            $q->where('statut_dossier', 'Exécuté');
        })->count();

        // 📊 RÉCLAMATIONS
        $totalReclamations = Reclamation::count();

        $reclamationsRecues = Reclamation::whereHas('statut', function ($q) {
            $q->where('statut_reclamation', 'Reçue');
        })->count();

        $reclamationsEnCours = Reclamation::whereHas('statut', function ($q) {
            $q->where('statut_reclamation', 'En cours');
        })->count();

        $reclamationsCloturees = Reclamation::whereHas('statut', function ($q) {
            $q->where('statut_reclamation', 'Clôturée');
        })->count();

        // ⏰ AUDIENCES PROCHES (3 jours)
        $audiencesProches = Audience::whereDate('date_prochaine_audience', '<=', Carbon::now()->addDays(3))
            ->whereDate('date_prochaine_audience', '>=', Carbon::now())
            ->count();

        // ⚖️ JUGEMENTS NON DEFINITIFS
        $jugementsNonDefinitifs = Jugement::where('est_definitif', false)->count();

        // ⚠️ RECLAMATIONS SANS ACTION (simple version)
        $reclamationsEnAttente = Reclamation::whereHas('statut', function ($q) {
            $q->whereIn('statut_reclamation', ['Reçue', 'En cours']);
        })->count();

        return view('dashboard.index', compact(
            'totalDossiers',
            'dossiersEnCours',
            'dossiersJuges',
            'dossiersExecutes',
            'totalReclamations',
            'reclamationsRecues',
            'reclamationsEnCours',
            'reclamationsCloturees',
            'audiencesProches',
            'jugementsNonDefinitifs',
            'reclamationsEnAttente'
        ));
    }
}