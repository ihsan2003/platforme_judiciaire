<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tribunaux', function (Blueprint $table) {
            $table->foreignId('id_parent')->nullable()->constrained('tribunaux')->onDelete('set null')->after('id_degre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tribunaux', function (Blueprint $table) {
            $table->dropForeign(['id_parent']);
            $table->dropColumn('id_parent');
        });
    }
};
