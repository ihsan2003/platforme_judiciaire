<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historique des statuts d'un dossier.
     *
     * Une ligne = "le dossier X a eu le statut Y de date_debut à date_fin".
     * date_fin = NULL signifie que c'est le statut actuellement actif.
     *
     * Sert à reconstruire le statut d'un dossier "tel qu'il était" à une
     * date donnée (ex : la fin d'une période de rapport statistique),
     * ce que le simple champ dossier_judiciaires.id_statut_dossier ne
     * permet pas (il n'expose que le statut courant).
     */
    public function up(): void
    {
        Schema::create('statut_dossier_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier')->constrained('dossier_judiciaires')->cascadeOnDelete();
            $table->foreignId('id_statut_dossier')->constrained('statut_dossiers');
            $table->timestamp('date_debut');
            $table->timestamp('date_fin')->nullable();
            $table->timestamps();

            $table->index(['id_dossier', 'date_debut']);
            $table->index(['id_dossier', 'date_fin']);
        });

        // Backfill : on ne connaît pas les transitions passées des dossiers
        // déjà existants, donc on initialise pour chacun une seule ligne
        // d'historique avec le statut ACTUEL, ouverte depuis date_ouverture
        // (ou created_at à défaut). Ce n'est qu'une approximation pour les
        // dossiers créés avant cette migration : leurs éventuels
        // changements de statut passés sont perdus, faute de traçabilité
        // antérieure. Seules les transitions à partir de maintenant seront
        // fiables.
        $now = now();

        DB::table('dossier_judiciaires')
            ->whereNotNull('id_statut_dossier')
            ->orderBy('id')
            ->chunk(500, function ($dossiers) use ($now) {
                $rows = $dossiers->map(fn ($d) => [
                    'id_dossier'         => $d->id,
                    'id_statut_dossier'  => $d->id_statut_dossier,
                    'date_debut'         => $d->date_ouverture ?? $d->created_at ?? $now,
                    'date_fin'           => null,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ])->toArray();

                DB::table('statut_dossier_historiques')->insert($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('statut_dossier_historiques');
    }
};