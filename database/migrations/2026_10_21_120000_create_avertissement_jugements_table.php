<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avertissement_jugements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_avertissement')->constrained('avertissements')->cascadeOnDelete();
            $table->foreignId('id_juge')->nullable()->constrained('juges')->nullOnDelete();
            $table->foreignId('id_type_jugement')->constrained('type_jugements');
            $table->date('date_jugement');
            $table->text('contenu_dispositif');
            $table->boolean('est_definitif')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avertissement_jugements');
    }
};
