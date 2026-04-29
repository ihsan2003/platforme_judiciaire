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
        Schema::table('degre_juridictions', function (Blueprint $table) {
            $table->unsignedTinyInteger('ordre')->default(1)->after('degre_juridiction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degre_juridictions', function (Blueprint $table) {
            //
        });
    }
};
