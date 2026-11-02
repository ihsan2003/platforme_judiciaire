<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeStructure;

class TypeStructureSeeder extends Seeder
{
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
            'محامي',
            'اخر',
        ];

        foreach ($types as $type) {
            TypeStructure::updateOrCreate(
                [
                    'type_structure' => $type,
                ],
                []
            );
        }

        $this->command->info(
            count($types) . ' types de structures créés ou vérifiés.'
        );
    }
}
