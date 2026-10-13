<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const TYPE_PREPARATOIRE = 'حكم تمهيدي';
    public const TYPE_AU_FOND      = 'حكم فاصل في الموضوع';

    public function up(): void
    {
        if (! Schema::hasTable('type_jugements')) {
            Schema::create('type_jugements', function (Blueprint $table) {
                $table->id();
                $table->string('type_jugement')->unique();
                $table->timestamps();
            });
        }

        // Les deux valeurs sont insérées ici (et non dans DataSeeder) pour que
        // les bases existantes les reçoivent sans avoir à relancer un seeder.
        foreach ([self::TYPE_AU_FOND, self::TYPE_PREPARATOIRE] as $libelle) {
            DB::table('type_jugements')->updateOrInsert(
                ['type_jugement' => $libelle],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        if (! Schema::hasColumn('jugements', 'id_type_jugement')) {
            $idFond = DB::table('type_jugements')
                ->where('type_jugement', self::TYPE_AU_FOND)
                ->value('id');

            Schema::table('jugements', function (Blueprint $table) use ($idFond) {
                // Les jugements existants sont tous des jugements sur le fond.
                $table->foreignId('id_type_jugement')
                    ->default($idFond)
                    ->after('id_juge')
                    ->constrained('type_jugements');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jugements', 'id_type_jugement')) {
            Schema::table('jugements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('id_type_jugement');
            });
        }

        Schema::dropIfExists('type_jugements');
    }
};
