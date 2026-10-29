<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le numéro de dossier est attribué par chaque juridiction : il appartient à
 * l'instance (dossier_tribunaux), pas au dossier.
 *
 * Forme canonique stockée, sans espaces :
 *   - 1re instance / appel : année/code/ordre          ex. 2024/7120/1994
 *   - cassation            : année/هيئة/غرفة/ordre      ex. 2025/1/4/6327
 *
 * L'ancienne colonne dossier_judiciaires.numero_dossier_tribunal est conservée
 * (lecture seule, repli d'affichage) pour ne perdre aucune donnée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_tribunaux', function (Blueprint $table) {
            $table->string('numero_dossier_tribunal', 50)->nullable()->after('id_commune');
            // Index simple (pas unique) : opposition / révision recréent une instance
            // au même tribunal avec le même numéro. L'unicité par dossier est
            // contrôlée dans NumeroDossierValide.
            $table->index(['id_tribunal', 'numero_dossier_tribunal'], 'dt_tribunal_numero_idx');
        });

        $this->reprendreAnciensNumeros();
    }

    public function down(): void
    {
        Schema::table('dossier_tribunaux', function (Blueprint $table) {
            $table->dropIndex('dt_tribunal_numero_idx');
            $table->dropColumn('numero_dossier_tribunal');
        });
    }

    /**
     * Copie l'ancien numéro (saisi au niveau du dossier) vers la première
     * instance du dossier. Un dossier sans instance garde son ancien numéro
     * en repli (voir DossierJudiciaire::getNumeroDossierTribunalAttribute).
     */
    private function reprendreAnciensNumeros(): void
    {
        DB::table('dossier_judiciaires')
            ->whereNotNull('numero_dossier_tribunal')
            ->where('numero_dossier_tribunal', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($dossiers) {
                foreach ($dossiers as $dossier) {
                    $premiere = DB::table('dossier_tribunaux')
                        ->where('id_dossier', $dossier->id)
                        ->orderBy('date_debut')
                        ->orderBy('id')
                        ->first();

                    if (! $premiere) {
                        continue;
                    }

                    DB::table('dossier_tribunaux')
                        ->where('id', $premiere->id)
                        ->update([
                            'numero_dossier_tribunal' => preg_replace('/\s+/u', '', $dossier->numero_dossier_tribunal),
                        ]);
                }
            });
    }
};
