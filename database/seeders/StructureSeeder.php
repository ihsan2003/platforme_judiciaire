<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Structure;

class StructureSeeder extends Seeder
{
    public function run(): void
    {
        // 🟢 1. Directeur (racine)
        Structure::create([
            'nom' => 'الإدارة المركزية',
            'id_type_structure' => 5, // إدارة مركزية
            'id_parent' => null
        ]);


        // 🟡 3. المفتشية
        Structure::create([
            'nom' => 'المفتشية',
            'id_type_structure' => 7,
            'id_parent' => 1
        ]);


        // 🟡 4. المديريات الجهوية
        Structure::create([
            'nom' => 'المديريات الجهوية',
            'id_type_structure' => 4,
            'id_parent' => 1
        ]);

        Structure::create([
            'nom' => 'المديريات الإقليمية',
            'id_type_structure' => 3,
            'id_parent' => 3
        ]);

        // 🟣 5. مديرية الشؤون الإدارية والمالية
        Structure::create([
            'nom' => 'المديرية الفرعية المكلفة بالشؤون الإدارية والمالية',
            'id_type_structure' => 6,
            'id_parent' => 1
        ]);

        Structure::create([
            'nom' => 'قسم الموارد البشرية',
            'id_type_structure' => 2,
            'id_parent' => 5
        ]);

        Structure::create([
            'nom' => 'مصلحة تدبير شؤون الموظفين',
            'id_type_structure' => 1,
            'id_parent' => 6
        ]);
    }
}