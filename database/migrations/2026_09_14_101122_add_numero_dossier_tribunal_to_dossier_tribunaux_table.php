<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chaque instance (dossier_tribunaux) — ابتدائي، استئناف، ... — a son
 * propre numéro de dossier attribué par le tribunal correspondant.
 * Jusqu'ici, seul le numéro de la première instance était stocké,
 * sur dossier_judiciaires.numero_dossier_tribunal (inchangé, conservé
 * pour compatibilité). Ce numéro par instance permet de générer et
 * afficher un numéro propre à chaque degré (notamment l'appel).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_tribunaux', function (Blueprint $table) {
            $table->string('numero_dossier_tribunal')->nullable()->after('id_degre');
        });
    }

    public function down(): void
    {
        Schema::table('dossier_tribunaux', function (Blueprint $table) {
            $table->dropColumn('numero_dossier_tribunal');
        });
    }
};