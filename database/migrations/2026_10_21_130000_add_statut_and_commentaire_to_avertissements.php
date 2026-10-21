<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const DEFAUT = 'في طور الإجراء';

    public const STATUTS = ['في طور الإجراء', 'تم التوصل بالانذار', 'لم يتم التوصل بالانذار', 'حفظ'];

    public function up(): void
    {
        if (! Schema::hasTable('statut_avertissements')) {
            Schema::create('statut_avertissements', function (Blueprint $table) {
                $table->id();
                $table->string('statut_avertissement')->unique();
                $table->timestamps();
            });
        }

        // Valeurs insérées ici (comme pour type_jugements) pour que les bases
        // existantes les reçoivent sans relancer de seeder.
        foreach (self::STATUTS as $libelle) {
            DB::table('statut_avertissements')->updateOrInsert(
                ['statut_avertissement' => $libelle],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $idDefaut = DB::table('statut_avertissements')
            ->where('statut_avertissement', self::DEFAUT)
            ->value('id');

        Schema::table('avertissements', function (Blueprint $table) use ($idDefaut) {
            // Les avertissements existants passent au statut par défaut « في طور الإجراء ».
            $table->foreignId('id_statut_avertissement')
                ->default($idDefaut)
                ->after('id_province')
                ->constrained('statut_avertissements');

            $table->text('commentaire')->nullable()->after('action_service');
        });
    }

    public function down(): void
    {
        Schema::table('avertissements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_statut_avertissement');
            $table->dropColumn('commentaire');
        });

        Schema::dropIfExists('statut_avertissements');
    }
};
