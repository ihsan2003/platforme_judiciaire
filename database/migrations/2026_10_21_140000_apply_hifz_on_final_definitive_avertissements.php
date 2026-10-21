<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rattrapage : les avertissements qui ont déjà un jugement final définitif
 * (définitif et non préparatoire) passent au statut « حفظ ».
 */
return new class extends Migration
{
    public function up(): void
    {
        $idClassement = DB::table('statut_avertissements')->where('statut_avertissement', 'حفظ')->value('id');
        $idPrep       = DB::table('type_jugements')->where('type_jugement', 'حكم تمهيدي')->value('id');

        if (! $idClassement) {
            return;
        }

        $ids = DB::table('avertissement_jugements')
            ->where('est_definitif', true)
            ->when($idPrep, fn ($q) => $q->where('id_type_jugement', '!=', $idPrep))
            ->pluck('id_avertissement');

        DB::table('avertissements')->whereIn('id', $ids)->update(['id_statut_avertissement' => $idClassement]);
    }

    public function down(): void
    {
        // Irréversible : l'ancien statut n'est pas conservé.
    }
};
