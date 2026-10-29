<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_judiciaires', function (Blueprint $table) {
            $table->string('numero_dossier_tribunal')->nullable()->change();
        });
    }

    public function down(): void
    {
    }
};