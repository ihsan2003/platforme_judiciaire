<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tribunal;

class HierarchieTribunauxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Exemple Administratif : Béni Mellal -> Marrakech
        $tpiAdminBeniMellal = Tribunal::where('nom_tribunal', 'LIKE', '%المحكمة الابتدائية الإدارية ببني ملال%')->first();
        $caAdminMarrakech = Tribunal::where('nom_tribunal', 'LIKE', '%محكمة الاستئناف الإدارية بمراكش%')->first();

        if ($tpiAdminBeniMellal && $caAdminMarrakech) {
            $tpiAdminBeniMellal->update(['id_parent' => $caAdminMarrakech->id]);
        }

        // Exemple Simple : Rabat -> Rabat
        $tpiRabat = Tribunal::where('nom_tribunal', 'LIKE', '%المحكمة الابتدائية بالرباط%')->first();
        $caRabat = Tribunal::where('nom_tribunal', 'LIKE', '%محكمة الاستئناف بالرباط%')->first();

        if ($tpiRabat && $caRabat) {
            $tpiRabat->update(['id_parent' => $caRabat->id]);
        }
        
        // On peut continuer ainsi pour toute la carte judiciaire
    }
}
