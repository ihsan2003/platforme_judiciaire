<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Même principe que statut_dossier_historiques, pour les réclamations.
     * Voir 2026_09_07_000001_create_statut_dossier_historiques_table.php.
     */
    public function up(): void
    {
        Schema::create('statut_reclamation_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reclamation')->constrained('reclamations')->cascadeOnDelete();
            $table->foreignId('id_statut_reclamation')->constrained('statut_reclamations');
            $table->timestamp('date_debut');
            $table->timestamp('date_fin')->nullable();
            $table->timestamps();

            $table->index(['id_reclamation', 'date_debut']);
            $table->index(['id_reclamation', 'date_fin']);
        });

        $now = now();

        DB::table('reclamations')
            ->whereNotNull('id_statut_reclamation')
            ->orderBy('id')
            ->chunk(500, function ($reclamations) use ($now) {
                $rows = $reclamations->map(fn ($r) => [
                    'id_reclamation'        => $r->id,
                    'id_statut_reclamation' => $r->id_statut_reclamation,
                    'date_debut'            => $r->date_reception ?? $r->created_at ?? $now,
                    'date_fin'              => null,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ])->toArray();

                DB::table('statut_reclamation_historiques')->insert($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('statut_reclamation_historiques');
    }
};