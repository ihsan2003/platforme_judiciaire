<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['region' => 'جهة طنجة - تطوان - الحسيمة'],
            ['region' => 'جهة الشرق'],
            ['region' => 'جهة فاس - مكناس'],
            ['region' => 'جهة الرباط - سلا - القنيطرة'],
            ['region' => 'جهة بني ملال - خنيفرة'],
            ['region' => 'جهة الدار البيضاء - سطات'],
            ['region' => 'جهة مراكش - آسفي'],
            ['region' => 'جهة درعة - تافيلالت'],
            ['region' => 'جهة سوس - ماسة'],
            ['region' => 'جهة كلميم - واد نون'],
            ['region' => 'جهة العيون - الساقية الحمراء'],
            ['region' => 'جهة الداخلة - وادي الذهب'],
        ];

        DB::table('regions')->insert($regions);
    }
}