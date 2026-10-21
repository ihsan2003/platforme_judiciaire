<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * « الحكم موضوع النزاع » devient « موضوع النزاع » : on aligne le nom de la
     * colonne sur celui de dossier_judiciaires (objet_litige).
     */
    public function up(): void
    {
        Schema::table('avertissements', function (Blueprint $table) {
            $table->renameColumn('decision_objet', 'objet_litige');
        });
    }

    public function down(): void
    {
        Schema::table('avertissements', function (Blueprint $table) {
            $table->renameColumn('objet_litige', 'decision_objet');
        });
    }
};
