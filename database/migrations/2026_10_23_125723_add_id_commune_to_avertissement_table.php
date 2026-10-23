<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avertissements', function (Blueprint $table) {
            // الجماعة الترابية (facultative : anciens avertissements sans commune)
            $table->foreignId('id_commune')
                  ->nullable()
                  ->after('id_province')
                  ->constrained('communes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('avertissements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_commune');
        });
    }
};