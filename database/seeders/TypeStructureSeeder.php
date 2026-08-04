<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeStructure;

class TypeStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'مصلحة',
            'قسم',
            'إدارة مركزية',
            'مديرية إقليمية',
            'مديرية جهوية',
            'مديرية فرعية',
            'مفتشية',
        ];

        foreach ($types as $type) {
            TypeStructure::firstOrCreate([
                'type_structure' => $type,
            ]);
        }
    }
}