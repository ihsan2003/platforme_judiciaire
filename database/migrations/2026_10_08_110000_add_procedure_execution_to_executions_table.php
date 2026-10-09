<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le champ « مسطرة التنفيذ » (procédure d'exécution) à la table executions.
     *
     * Ce champ remplace l'ancien champ « observations » des formulaires, qui n'avait
     * jamais de colonne en base : sa valeur était silencieusement ignorée à l'enregistrement.
     */
    public function up(): void
    {
        Schema::table('executions', function (Blueprint $table) {
            $table->text('procedure_execution')->nullable()->after('date_execution');
        });
    }

    public function down(): void
    {
        Schema::table('executions', function (Blueprint $table) {
            $table->dropColumn('procedure_execution');
        });
    }
};
