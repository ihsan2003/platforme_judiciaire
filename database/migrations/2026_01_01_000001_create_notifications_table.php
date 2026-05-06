<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('users')->cascadeOnDelete();

            $table->string('type_notification');
            $table->enum('niveau', ['info', 'warning', 'danger'])->default('info');
            $table->string('message');
            $table->text('details')->nullable();

            $table->foreignId('id_dossier')->nullable()->constrained('dossier_judiciaires')->nullOnDelete();
            $table->foreignId('id_audience')->nullable()->constrained('audiences')->nullOnDelete();
            $table->foreignId('id_jugement')->nullable()->constrained('jugements')->nullOnDelete();
            $table->foreignId('id_reclamation')->nullable()->constrained('reclamations')->nullOnDelete();
            $table->foreignId('id_execution')->nullable()->constrained('executions')->nullOnDelete();

            $table->string('url_action')->nullable();
            $table->boolean('est_lue')->default(false);
            $table->timestamp('date_lecture')->nullable();

            // Clé de déduplication — préfixée par "uX_" pour inclure l'utilisateur
            // L'index composite (id_utilisateur, cle_dedup) garantit l'unicité par personne
            $table->string('cle_dedup')->nullable();

            $table->timestamps();

            // ⚠️  Index composite : remplace l'ancien unique() sur cle_dedup seul
            $table->unique(['id_utilisateur', 'cle_dedup'], 'notif_user_dedup_unique');

            $table->index(['id_utilisateur', 'est_lue']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};