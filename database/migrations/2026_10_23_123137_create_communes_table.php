<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            // الجماعة الترابية
            $table->string('commune');
            // الإقليم / العمالة
            $table->foreignId('id_province')->constrained('provinces')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['id_province', 'commune']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};