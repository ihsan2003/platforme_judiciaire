<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_chambres', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();      // 'jinayat' | 'junah'
            $table->string('type_chambre');        // جنايات | جنح
            $table->timestamps();
        });

        Schema::create('chambres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tribunal')->constrained('tribunaux')->cascadeOnDelete();
            $table->foreignId('id_type_chambre')->constrained('type_chambres');
            $table->unsignedTinyInteger('numero');  // 1, 2…
            $table->string('chambre');              // غرفة الجنايات الأولى
            $table->timestamps();
            $table->unique(['id_tribunal', 'id_type_chambre', 'numero']);
        });

        Schema::table('dossier_tribunaux', function (Blueprint $table) {
            // الغرفة (facultative : anciennes instances sans chambre)
            $table->foreignId('id_chambre')
                  ->nullable()
                  ->after('id_commune')
                  ->constrained('chambres')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dossier_tribunaux', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_chambre');
        });
        Schema::dropIfExists('chambres');
        Schema::dropIfExists('type_chambres');
    }
};
