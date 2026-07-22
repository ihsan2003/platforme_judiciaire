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
        Schema::create('tribunal_appel_relations', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tribunal_premier_degre_id')->constrained('tribunaux')->onDelete('cascade');
            $blueprint->foreignId('tribunal_appel_id')->constrained('tribunaux')->onDelete('cascade');
            $blueprint->foreignId('type_tribunal_id')->constrained('type_tribunaux')->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tribunal_appel_relations');
    }
};
