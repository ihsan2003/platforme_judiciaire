<?php
// database/migrations/2026_07_17_000001_add_id_type_reclamation_to_reclamations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->foreignId('id_type_reclamation')
                ->nullable()
                ->after('id_reclamant')
                ->constrained('type_reclamations')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('reclamations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_type_reclamation');
        });
    }
};