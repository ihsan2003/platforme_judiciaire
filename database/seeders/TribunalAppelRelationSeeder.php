<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tribunal;
use App\Models\TypeTribunal;
use App\Models\TribunalAppelRelation;
use Illuminate\Support\Facades\DB;

class TribunalAppelRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // IDs des types de tribunaux basés sur DataSeeder
        $typeSimpleId = 1; // المحكمة الابتدائية
        $typeAppelId = 2; // محكمة الاستئناف
        $typeAdminId = 4; // المحكمة الإدارية
        $typeAppelAdminId = 5; // محكمة الاستئناف الإدارية
        $typeComId = 6; // المحكمة التجارية
        $typeAppelComId = 7; // محكمة الاستئناف التجارية

        // 1. Exemple Administratif : Béni Mellal -> Marrakech
        $tpiAdminBeniMellal = Tribunal::where('nom_tribunal', 'LIKE', '%المحكمة الابتدائية الإدارية ببني ملال%')->first();
        $caAdminMarrakech = Tribunal::where('nom_tribunal', 'LIKE', '%محكمة الاستئناف الإدارية بمراكش%')->first();

        if ($tpiAdminBeniMellal && $caAdminMarrakech) {
            TribunalAppelRelation::create([
                'tribunal_premier_degre_id' => $tpiAdminBeniMellal->id,
                'tribunal_appel_id' => $caAdminMarrakech->id,
                'type_tribunal_id' => $typeAdminId
            ]);
        }

        // 2. Exemple Simple : Rabat -> Rabat
        $tpiRabat = Tribunal::where('nom_tribunal', 'LIKE', '%المحكمة الابتدائية بالرباط%')->first();
        $caRabat = Tribunal::where('nom_tribunal', 'LIKE', '%محكمة الاستئناف بالرباط%')->first();

        if ($tpiRabat && $caRabat) {
            TribunalAppelRelation::create([
                'tribunal_premier_degre_id' => $tpiRabat->id,
                'tribunal_appel_id' => $caRabat->id,
                'type_tribunal_id' => $typeSimpleId
            ]);
        }

        // 3. Exemple Commercial : Tanger -> Fès (Hypothétique selon l'exemple de l'utilisateur)
        $tpiComTanger = Tribunal::where('nom_tribunal', 'LIKE', '%المحكمة الابتدائية التجارية بطنجة%')->first();
        $caComFes = Tribunal::where('nom_tribunal', 'LIKE', '%محكمة الاستئناف التجارية بفاس%')->first();

        if ($tpiComTanger && $caComFes) {
            TribunalAppelRelation::create([
                'tribunal_premier_degre_id' => $tpiComTanger->id,
                'tribunal_appel_id' => $caComFes->id,
                'type_tribunal_id' => $typeComId
            ]);
        }
        
        // On pourrait ajouter ici toutes les relations basées sur la carte judiciaire marocaine
    }
}
