<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un document peut être rattaché soit à un dossier (id_dossier),
     * soit à une réclamation (id_reclamation) — jamais les deux étant
     * obligatoires en même temps. La migration précédente avait rendu
     * id_reclamation NOT NULL sans valeur par défaut, ce qui empêchait
     * tout upload de document sur un dossier seul.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['id_reclamation']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('id_reclamation')
                ->nullable()
                ->change();

            $table->foreign('id_reclamation')
                ->references('id')->on('reclamations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['id_reclamation']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('id_reclamation')
                ->nullable(false)
                ->change();

            $table->foreign('id_reclamation')
                ->references('id')->on('reclamations')
                ->cascadeOnDelete();
        });
    }
};