<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Structure;
use App\Models\TypeStructure;
use App\Models\Region;
use App\Models\Province;

class StructureSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération des types de structures
        $types = TypeStructure::pluck('id', 'type_structure');

        // Vérification des types nécessaires
        $requiredTypes = [
            'إدارة مركزية',
            'مفتشية',
            'مديرية جهوية',
            'مديرية إقليمية',
        ];

        foreach ($requiredTypes as $type) {
            if (!isset($types[$type])) {
                throw new \Exception("Type de structure introuvable : {$type}");
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Direction centrale
        |--------------------------------------------------------------------------
        */

        $directionCentrale = Structure::updateOrCreate(
            [
                'nom' => 'الإدارة المركزية',
            ],
            [
                'id_type_structure' => $types['إدارة مركزية'],
                'id_parent' => null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Inspection
        |--------------------------------------------------------------------------
        */

        Structure::updateOrCreate(
            [
                'nom' => 'المفتشية',
            ],
            [
                'id_type_structure' => $types['مفتشية'],
                'id_parent' => $directionCentrale->id,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Directions régionales
        |--------------------------------------------------------------------------
        */

        $regionsStructures = [];

        Region::orderBy('id')->get()->each(function ($region) use (
            $types,
            $directionCentrale,
            &$regionsStructures
        ) {

            $structure = Structure::updateOrCreate(
                [
                    'nom' => 'المديرية الجهوية ل' . $region->region,
                ],
                [
                    'id_type_structure' => $types['مديرية جهوية'],
                    'id_parent' => $directionCentrale->id,
                ]
            );

            // Association ID région => ID structure régionale
            $regionsStructures[$region->id] = $structure->id;
        });

        /*
        |--------------------------------------------------------------------------
        | Directions provinciales
        |--------------------------------------------------------------------------
        */

        Province::orderBy('id')->get()->each(function ($province) use (
            $types,
            $regionsStructures
        ) {

            // Récupération de la direction régionale par id_region
            $parentRegional = $regionsStructures[$province->id_region] ?? null;

            if (!$parentRegional) {
                throw new \Exception(
                    "Direction régionale introuvable pour la province : {$province->province}"
                );
            }

            Structure::updateOrCreate(
                [
                    'nom' => 'المديرية الإقليمية ب' . $province->province,
                ],
                [
                    'id_type_structure' => $types['مديرية إقليمية'],
                    'id_parent' => $parentRegional,
                ]
            );
        });

        $this->command->info('Structures créées ou mises à jour avec succès.');
    }
}
