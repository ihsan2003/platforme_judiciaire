<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Juge;
use App\Models\Tribunal;

class JugeSeeder extends Seeder
{
    public function run(): void
    {
        $noms = [
            'محمد العلوي',
            'أحمد بنعلي',
            'يوسف الإدريسي',
            'عبد الرحمان الفاسي',
            'سعيد المريني',
            'خالد الزهراني',
            'مصطفى أمين',
            'إدريس بنعبد الله',
            'رشيد التازي',
            'عمر الحسني',
            'نبيل الشاوي',
            'حسن الكيلاني',
            'طارق السالمي',
            'عبد الله المرابط',
            'أيوب العمراوي',
            'حمزة القادري',
            'منير الودغيري',
            'صلاح الدين المنصوري',
            'ياسين بنعمر',
            'كمال العيساوي',
        ];

        $grades = [
            'رئيس غرفة',
            'مستشار بمحكمة الاستئناف',
            'قاضي من الدرجة الأولى',
            'قاضي بالمحكمة الابتدائية',
            'نائب رئيس المحكمة',
            'رئيس المحكمة',
        ];

        $specialisations = [
            'القضاء المدني',
            'القضاء الجنحي',
            'القضاء التجاري',
            'قضاء الأسرة',
            'القضاء الإداري',
            'القضايا العقارية',
            'القضايا الاجتماعية',
        ];


        $tribunaux = Tribunal::all();


        foreach ($tribunaux as $tribunal) {

            // عدد القضاة حسب نوع المحكمة
            $nombreJuges = match($tribunal->id_type_tribunal) {

                // محاكم ابتدائية
                1 => rand(5, 10),

                // محاكم الاستئناف
                2 => rand(10, 20),

                // المحاكم التجارية
                3 => rand(6, 12),

                // استئناف تجاري
                4 => rand(8, 15),

                // المحاكم الإدارية
                5 => rand(5, 10),

                // استئناف إداري
                6 => rand(8, 15),

                // محكمة النقض
                7 => rand(20, 30),

                default => 5,
            };


            for ($i = 0; $i < $nombreJuges; $i++) {

                $nom = $noms[array_rand($noms)];

                Juge::firstOrCreate(
                    [
                        'nom_complet' => $nom,
                        'id_tribunal' => $tribunal->id,
                    ],
                    [
                        'grade' => $grades[array_rand($grades)],
                        'specialisation' => $specialisations[array_rand($specialisations)],
                    ]
                );
            }
        }
    }
}