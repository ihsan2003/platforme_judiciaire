<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une partie, dans un dossier donné, peut avoir plusieurs avocats :
        //  - id_degre = NULL  → l'avocat intervient durant tout le processus du dossier
        //  - id_degre = X     → l'avocat intervient seulement à ce degré (1ère instance, appel, cassation...)
        Schema::create('dossier_partie_avocats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dossier_partie')->constrained('dossier_parties')->cascadeOnDelete();
            $table->foreignId('id_avocat')->constrained('avocats');
            $table->foreignId('id_degre')->nullable()->constrained('degre_juridictions')->nullOnDelete();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamps();

            $table->index(['id_dossier_partie', 'id_degre']);
        });

        // Reprise de l'existant : l'avocat actuellement lié à la partie (parties.id_avocat)
        // devient un avocat "tout le processus" dans chacun de ses dossiers.
        $rows = DB::table('dossier_parties as dp')
            ->join('parties as p', 'p.id', '=', 'dp.id_partie')
            ->whereNotNull('p.id_avocat')
            ->get(['dp.id as id_dossier_partie', 'p.id_avocat', 'dp.date_entree']);

        foreach ($rows->chunk(500) as $chunk) {
            DB::table('dossier_partie_avocats')->insert(
                $chunk->map(fn ($r) => [
                    'id_dossier_partie' => $r->id_dossier_partie,
                    'id_avocat'         => $r->id_avocat,
                    'id_degre'          => null,
                    'date_debut'        => $r->date_entree,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ])->all()
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_partie_avocats');
    }
};
