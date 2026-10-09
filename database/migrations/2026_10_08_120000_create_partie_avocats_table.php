<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une partie peut avoir plusieurs avocats au fil de la procédure.
     *
     * Chaque ligne de `partie_avocats` est une affectation :
     *   - id_dossier NULL  → l'avocat représente la partie dans tous ses dossiers
     *                        (c'est l'ancien comportement de parties.id_avocat)
     *   - id_dossier rempli → l'avocat intervient seulement dans ce dossier
     *   - id_degre NULL    → pour tous les degrés de juridiction
     *   - id_degre rempli  → seulement pour ce degré (1ère instance, appel, cassation…)
     */
    public function up(): void
    {
        Schema::create('partie_avocats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_partie')->constrained('parties')->cascadeOnDelete();
            $table->foreignId('id_avocat')->constrained('avocats')->cascadeOnDelete();
            $table->foreignId('id_dossier')->nullable()->constrained('dossier_judiciaires')->cascadeOnDelete();
            $table->foreignId('id_degre')->nullable()->constrained('degre_juridictions');
            $table->timestamps();

            $table->index(['id_partie', 'id_dossier']);
            $table->index('id_avocat');
        });

        // Reprise des données : l'ancien lien permanent parties.id_avocat devient
        // une affectation générale (tous dossiers, tous degrés).
        $now = now();

        $existants = DB::table('parties')
            ->whereNotNull('id_avocat')
            ->get(['id', 'id_avocat'])
            ->map(fn ($p) => [
                'id_partie'  => $p->id,
                'id_avocat'  => $p->id_avocat,
                'id_dossier' => null,
                'id_degre'   => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        foreach (array_chunk($existants, 500) as $chunk) {
            DB::table('partie_avocats')->insert($chunk);
        }

        Schema::table('parties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_avocat');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->foreignId('id_avocat')
                ->nullable()
                ->after('adresse')
                ->constrained('avocats')
                ->nullOnDelete();
        });

        // Retour en arrière : on ne peut conserver qu'un seul avocat par partie,
        // on garde la première affectation générale (sinon la plus ancienne).
        $affectations = DB::table('partie_avocats')
            ->orderByRaw('id_dossier IS NOT NULL')
            ->orderBy('id')
            ->get(['id_partie', 'id_avocat']);

        $vus = [];
        foreach ($affectations as $a) {
            if (isset($vus[$a->id_partie])) {
                continue;
            }
            $vus[$a->id_partie] = true;
            DB::table('parties')->where('id', $a->id_partie)->update(['id_avocat' => $a->id_avocat]);
        }

        Schema::dropIfExists('partie_avocats');
    }
};
