<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_judiciaires', function (Blueprint $table) {
            $table->text('objet_litige')->nullable()->after('numero_dossier_tribunal');
            $table->text('action_service')->nullable()->after('objet_litige');
        });
    }

    public function down(): void
    {
        Schema::table('dossier_judiciaires', function (Blueprint $table) {
            $table->dropColumn(['objet_litige', 'action_service']);
        });
    }
};
