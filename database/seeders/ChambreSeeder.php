<?php

namespace Database\Seeders;

use App\Models\Chambre;
use App\Models\Tribunal;
use App\Models\TypeChambre;
use Illuminate\Database\Seeder;

class ChambreSeeder extends Seeder
{
    /**
     * Répartition des chambres par degré de juridiction (degre_juridictions.id) :
     *  - degré 1 : chambres des délits (الجنح)
     *  - degré 2 : chambres criminelles (الجنايات)
     */
    private const DEGRE_JUNAH   = 1;
    private const DEGRE_JINAYAT = 2;

    public function run(): void
    {
        $jinayat = TypeChambre::firstOrCreate(['code' => 'jinayat'], ['type_chambre' => 'جنايات']);
        $junah   = TypeChambre::firstOrCreate(['code' => 'junah'],   ['type_chambre' => 'جنح']);

        $parDegre = [
            self::DEGRE_JINAYAT => [
                [$jinayat, 1, 'غرفة الجنايات الأولى'],
                [$jinayat, 2, 'غرفة الجنايات الثانية'],
            ],
            self::DEGRE_JUNAH => [
                [$junah, 1, 'غرفة الجنح الأولى'],
                [$junah, 2, 'غرفة الجنح الثانية'],
            ],
        ];

        foreach ($parDegre as $idDegre => $definitions) {
            Tribunal::where('id_degre', $idDegre)->each(function (Tribunal $tribunal) use ($definitions) {
                foreach ($definitions as [$type, $numero, $libelle]) {
                    Chambre::firstOrCreate(
                        [
                            'id_tribunal'     => $tribunal->id,
                            'id_type_chambre' => $type->id,
                            'numero'          => $numero,
                        ],
                        ['chambre' => $libelle]
                    );
                }
            });
        }
    }
}