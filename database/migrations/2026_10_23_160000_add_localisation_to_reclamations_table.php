<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            // الإقليم / العمالة — la région (الجهة) en est déduite via la province.
            // Facultatif : les réclamations existantes n'ont pas de localisation.
            $table->foreignId('id_province')
                  ->nullable()
                  ->constrained('provinces')
                  ->nullOnDelete();

            // الجماعة الترابية (facultative)
            $table->foreignId('id_commune')
                  ->nullable()
                  ->after('id_province')
                  ->constrained('communes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_commune');
            $table->dropConstrainedForeignId('id_province');
        });
    }
};
