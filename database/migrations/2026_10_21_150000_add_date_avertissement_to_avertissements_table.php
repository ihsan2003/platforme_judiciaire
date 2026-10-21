<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avertissements', function (Blueprint $table) {
            // Nullable : les avertissements existants n'ont pas de date.
            $table->date('date_avertissement')->nullable()->after('id_statut_avertissement');
        });
    }

    public function down(): void
    {
        Schema::table('avertissements', function (Blueprint $table) {
            $table->dropColumn('date_avertissement');
        });
    }
};
