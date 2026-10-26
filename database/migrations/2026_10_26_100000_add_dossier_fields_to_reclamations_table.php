<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            // Champs "dossier" : renseignés uniquement pour les réclamations de type
            // « شكايات مرتبطة بالمنازعات القضائية ». Tous facultatifs : les réclamations
            // existantes (et celles des autres types) n'en ont pas.
            $table->foreignId('id_degre')
                  ->nullable()
                  ->constrained('degre_juridictions')
                  ->nullOnDelete();

            $table->foreignId('id_tribunal')
                  ->nullable()
                  ->constrained('tribunaux')
                  ->nullOnDelete();

            $table->foreignId('id_type_affaire')
                  ->nullable()
                  ->constrained('type_affaires')
                  ->nullOnDelete();

            // المدعي : par défaut la partie institution (parties.est_entraide = true)
            $table->foreignId('id_demandeur')
                  ->nullable()
                  ->constrained('parties')
                  ->nullOnDelete();

            // المدعى عليه : par défaut le réclamant, converti en Partie
            $table->foreignId('id_defendeur')
                  ->nullable()
                  ->constrained('parties')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_defendeur');
            $table->dropConstrainedForeignId('id_demandeur');
            $table->dropConstrainedForeignId('id_type_affaire');
            $table->dropConstrainedForeignId('id_tribunal');
            $table->dropConstrainedForeignId('id_degre');
        });
    }
};
