<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Structure;
use App\Models\TypeStructure;
use App\Models\Region;
use App\Models\Province;

class StructureSeeder extends Seeder
{

    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = TypeStructure::pluck('id', 'type_structure');


        /*
        |--------------------------------------------------------------------------
        | Direction centrale (racine)
        |--------------------------------------------------------------------------
        */

        $directionCentrale = Structure::firstOrCreate(
            [
                'nom' => 'الإدارة المركزية'
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

        Structure::firstOrCreate(
            [
                'nom' => 'المفتشية'
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

            $structure = Structure::firstOrCreate(

                [
                    'nom' => 'المديرية الجهوية ل' . $region->region
                ],

                [
                    'id_type_structure' => $types['مديرية جهوية'],
                    'id_parent' => $directionCentrale->id,
                ]

            );


            // mémoriser la direction régionale correspondante
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


            $parentRegional = $regionsStructures[$province->id_region] ?? null;


            Structure::firstOrCreate(

                [
                    'nom' => 'المديرية الإقليمية ب' . $province->province
                ],

                [
                    'id_type_structure' => $types['مديرية إقليمية'],
                    'id_parent' => $parentRegional,
                ]

            );

        });

    }
}