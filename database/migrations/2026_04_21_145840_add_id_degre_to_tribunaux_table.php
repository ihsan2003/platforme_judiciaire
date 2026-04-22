<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tribunaux', function (Blueprint $table) {
            $table->foreignId('id_degre')
                  ->nullable()
                  ->after('id_type_tribunal')
                  ->constrained('degre_juridictions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tribunaux', function (Blueprint $table) {
            $table->dropForeign(['id_degre']);
            $table->dropColumn('id_degre');
        });
    }
};