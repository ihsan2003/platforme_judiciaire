<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Audience;
use App\Models\Execution;
use App\Models\Jugement;
use App\Models\Notification;
use App\Models\Reclamation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Génère et gère les notifications d'alerte basées sur les délais métier.
 *
 * Règles :
 *  - Audiences dans les 7 jours             → warning
 *  - Audiences aujourd'hui                  → danger
 *  - Délai de recours < 7 jours restants    → warning
 *  - Délai de recours = 0 (expire auj.)     → danger
 *  - Jugements non définitifs > 30 jours    → info
 *  - Réclamations en attente > 7 jours      → warning
 *  - Exécutions sans date de fin > 30 jours → warning
 */
class NotificationService
{
    // ─── Seuils configurables ─────────────────────────────────────────────

    private const SEUIL_AUDIENCE_JOURS       = 7;  // jours avant audience → alerte
    private const SEUIL_RECOURS_JOURS        = 29;  // jours restants pour recours → alerte
    private const SEUIL_RECLAMATION_JOURS    = 7;  // jours sans action → alerte
    private const SEUIL_JUGEMENT_JOURS       = 30; // jours sans définitif → alerte
    private const SEUIL_EXECUTION_JOURS      = 30; // jours d'exécution sans fin → alerte

    /**
     * Génère toutes les notifications pour tous les utilisateurs actifs.
     * Appelé par le job planifié quotidien.
     */
    public function genererToutesLesNotifications(): int
    {
        $utilisateurs = User::all();
        $total        = 0;

        foreach ($utilisateurs as $utilisateur) {
            $total += $this->genererPourUtilisateur($utilisateur);
        }

        return $total;
    }

    /**
     * Génère toutes les notifications pour un utilisateur donné.
     * Retourne le nombre de nouvelles notifications créées.
     */
    public function genererPourUtilisateur(User $utilisateur): int
    {
        $count = 0;

        $count += $this->alertesAudiences($utilisateur);
        $count += $this->alertesDelaisRecours($utilisateur);
        $count += $this->alertesJugementsNonDefinitifs($utilisateur);
        $count += $this->alertesReclamationsEnAttente($utilisateur);
        $count += $this->alertesExecutionsEnRetard($utilisateur);

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────
    // 1. AUDIENCES PROCHES
    // ─────────────────────────────────────────────────────────────────────

    private function alertesAudiences(User $utilisateur): int
    {
        $count = 0;

        $audiences = Audience::with([
                'dossierTribunal.dossier',
                'dossierTribunal.tribunal',
                'typeAudience',
                'juge',
            ])
            ->whereDate('date_audience', '>=', today())
            ->whereDate('date_audience', '<=', today()->addDays(self::SEUIL_AUDIENCE_JOURS))
            ->get();

        foreach ($audiences as $audience) {
            $joursRestants = today()->diffInDays($audience->date_audience);
            $estAujourdhui = $audience->date_audience->isToday();

            $niveau = $estAujourdhui ? 'danger' : ($joursRestants <= 2 ? 'warning' : 'info');

            $dossier  = $audience->dossierTribunal?->dossier;
            $tribunal = $audience->dossierTribunal?->tribunal?->nom_tribunal ?? '?';
            $type     = $audience->typeAudience?->type_audience ?? '';

            if ($estAujourdhui) {
                $message = "Audience aujourd'hui — {$dossier?->numero_dossier_interne}";
                $details = "Tribunal : {$tribunal} | Type : {$type}";
            } else {
                $message = "Audience dans {$joursRestants} jour" . ($joursRestants > 1 ? 's' : '') . " — {$dossier?->numero_dossier_interne}";
                $details = "Le {$audience->date_audience->format('d/m/Y')} | Tribunal : {$tribunal}";
            }

            $cleDedup = "audience_{$audience->id}_" . today()->toDateString();

            $created = $this->creerSiNouvelle($utilisateur, [
                'type_notification' => 'audience_proche',
                'niveau'            => $niveau,
                'message'           => $message,
                'details'           => $details,
                'id_dossier'        => $dossier?->id,
                'id_audience'       => $audience->id,
                'url_action'        => route('audiences.show', $audience->id),
                'cle_dedup'         => $cleDedup,
            ]);

            if ($created) {
                $count++;
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2. DÉLAIS DE RECOURS PROCHES
    // ─────────────────────────────────────────────────────────────────────

    private function alertesDelaisRecours(User $utilisateur): int
    {
        $count = 0;

        // Jugements non définitifs sans recours
        $jugements = Jugement::with([
                'dossierTribunal.dossier',
                'dossierTribunal.tribunal',
            ])
            ->where('est_definitif', false)
            ->doesntHave('recours')
            ->get();

        foreach ($jugements as $jugement) {
            $delaiRestant = $jugement->delai_recours_restant;

            // Pas de type de recours configuré ou délai déjà largement dépassé
            if ($delaiRestant === null || $delaiRestant < -30) {
                continue;
            }

            // On n'alerte que si on est dans la fenêtre critique
            if ($delaiRestant > self::SEUIL_RECOURS_JOURS) {
                continue;
            }

            $dossier  = $jugement->dossierTribunal?->dossier;
            $tribunal = $jugement->dossierTribunal?->tribunal?->nom_tribunal ?? '?';

            if ($delaiRestant <= 0) {
                $niveau  = 'danger';
                $message = "Délai de recours expiré — {$dossier?->numero_dossier_interne}";
                $details = "Jugement du {$jugement->date_jugement->format('d/m/Y')} | {$tribunal}";
            } else {
                $niveau  = $delaiRestant <= 3 ? 'danger' : 'warning';
                $message = "Délai de recours : {$delaiRestant} jour" . ($delaiRestant > 1 ? 's' : '') . " restant" . ($delaiRestant > 1 ? 's' : '') . " — {$dossier?->numero_dossier_interne}";
                $details = "Jugement du {$jugement->date_jugement->format('d/m/Y')} | {$tribunal}";
            }

            $cleDedup = "recours_{$jugement->id}_" . today()->toDateString();

            $created = $this->creerSiNouvelle($utilisateur, [
                'type_notification' => 'delai_recours',
                'niveau'            => $niveau,
                'message'           => $message,
                'details'           => $details,
                'id_dossier'        => $dossier?->id,
                'id_jugement'       => $jugement->id,
                'url_action'        => route('jugements.show', $jugement->id),
                'cle_dedup'         => $cleDedup,
            ]);

            if ($created) {
                $count++;
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3. JUGEMENTS NON DÉFINITIFS (anciens)
    // ─────────────────────────────────────────────────────────────────────

    private function alertesJugementsNonDefinitifs(User $utilisateur): int
    {
        $count = 0;

        $jugements = Jugement::with([
                'dossierTribunal.dossier',
            ])
            ->where('est_definitif', false)
            ->doesntHave('recours')
            ->whereDate('date_jugement', '<=', today()->subDays(self::SEUIL_JUGEMENT_JOURS))
            ->get();

        foreach ($jugements as $jugement) {
            $joursEcoules = $jugement->date_jugement->diffInDays(today());
            $dossier      = $jugement->dossierTribunal?->dossier;

            $message = "Jugement non définitif depuis {$joursEcoules} jours — {$dossier?->numero_dossier_interne}";
            $details = "Rendu le {$jugement->date_jugement->format('d/m/Y')} — aucun recours ni clôture enregistrée";

            $cleDedup = "jugement_ndf_{$jugement->id}_" . today()->format('Y-W'); // une fois par semaine

            $created = $this->creerSiNouvelle($utilisateur, [
                'type_notification' => 'jugement_non_definitif',
                'niveau'            => 'info',
                'message'           => $message,
                'details'           => $details,
                'id_dossier'        => $dossier?->id,
                'id_jugement'       => $jugement->id,
                'url_action'        => route('jugements.show', $jugement->id),
                'cle_dedup'         => $cleDedup,
            ]);

            if ($created) {
                $count++;
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────
    // 4. RÉCLAMATIONS EN ATTENTE DEPUIS TROP LONGTEMPS
    // ─────────────────────────────────────────────────────────────────────

    private function alertesReclamationsEnAttente(User $utilisateur): int
    {
        $count = 0;

        $reclamations = Reclamation::with(['reclamant', 'statut'])
            ->enAttente()
            // Aucune action depuis SEUIL_RECLAMATION_JOURS jours
            ->whereDoesntHave('actions', function ($q) {
                $q->whereDate('created_at', '>=', today()->subDays(self::SEUIL_RECLAMATION_JOURS));
            })
            ->whereDate('date_reception', '<=', today()->subDays(self::SEUIL_RECLAMATION_JOURS))
            ->get();

        foreach ($reclamations as $reclamation) {
            $joursAttente = $reclamation->date_reception->diffInDays(today());
            $nom          = $reclamation->reclamant?->nom ?? 'Inconnu';
            $statut       = $reclamation->statut?->statut_reclamation ?? '?';

            $niveau  = $joursAttente > 30 ? 'danger' : 'warning';
            $message = "Réclamation en attente depuis {$joursAttente} jours — {$nom}";
            $details = "Objet : {$reclamation->objet} | Statut : {$statut}";

            $cleDedup = "reclamation_{$reclamation->id}_" . today()->format('Y-W');

            $created = $this->creerSiNouvelle($utilisateur, [
                'type_notification' => 'reclamation_en_attente',
                'niveau'            => $niveau,
                'message'           => $message,
                'details'           => $details,
                'id_reclamation'    => $reclamation->id,
                'url_action'        => route('reclamations.show', $reclamation->id),
                'cle_dedup'         => $cleDedup,
            ]);

            if ($created) {
                $count++;
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────
    // 5. EXÉCUTIONS EN RETARD
    // ─────────────────────────────────────────────────────────────────────

    private function alertesExecutionsEnRetard(User $utilisateur): int
    {
        $count = 0;

        $executions = Execution::with([
                'jugement.dossierTribunal.dossier',
                'statut',
                'responsable',
            ])
            ->whereNull('date_execution')                                    // pas encore terminée
            ->whereDate('date_notification', '<=', today()->subDays(self::SEUIL_EXECUTION_JOURS))
            ->whereHas('statut', fn($q) => $q->where('statut_execution', '!=', 'Terminée'))
            ->get();

        foreach ($executions as $execution) {
            $joursEcoules = $execution->date_notification->diffInDays(today());
            $dossier      = $execution->jugement?->dossierTribunal?->dossier;

            $niveau  = $joursEcoules > 60 ? 'danger' : 'warning';
            $message = "Exécution en cours depuis {$joursEcoules} jours — {$execution->numero_dossier_execution}";
            $details = "Dossier : {$dossier?->numero_dossier_interne} | Notifiée le {$execution->date_notification->format('d/m/Y')}";

            $cleDedup = "execution_{$execution->id}_" . today()->format('Y-W');

            $created = $this->creerSiNouvelle($utilisateur, [
                'type_notification' => 'execution_en_retard',
                'niveau'            => $niveau,
                'message'           => $message,
                'details'           => $details,
                'id_dossier'        => $dossier?->id,
                'id_execution'      => $execution->id,
                'url_action'        => route('executions.show', $execution->id),
                'cle_dedup'         => $cleDedup,
            ]);

            if ($created) {
                $count++;
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Crée la notification seulement si la clé de déduplication n'existe pas encore.
     * La clé inclut l'id_utilisateur pour éviter les collisions entre utilisateurs.
     * Un try/catch absorbe les doublons en cas de race condition.
     * Retourne true si créée, false si déjà existante.
     */
    private function creerSiNouvelle(User $utilisateur, array $data): bool
    {
        // Inclure l'id utilisateur dans la clé pour qu'elle soit unique par personne
        if (!empty($data['cle_dedup'])) {
            $data['cle_dedup'] = 'u' . $utilisateur->id . '_' . $data['cle_dedup'];
        }

        try {
            Notification::create(array_merge($data, [
                'id_utilisateur' => $utilisateur->id,
                'est_lue'        => false,
            ]));

            return true;

        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Doublon attendu : la notification existe déjà pour aujourd'hui
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // NETTOYAGE
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Supprime les notifications lues de plus de 30 jours.
     */
    public function nettoyerAnciennesNotifications(int $joursRetention = 30): int
    {
        return Notification::where('est_lue', true)
            ->where('updated_at', '<=', now()->subDays($joursRetention))
            ->delete();
    }
}