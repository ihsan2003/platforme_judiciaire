<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TribunauxSeeder extends Seeder
{
    /**
     * Seed all Moroccan courts based on Decree 2.23.665
     * (Carte judiciaire du Royaume - November 2023)
     *
     * Types de tribunaux (id_type_tribunal):
     *   1 = محكمة ابتدائية
     *   2 = محكمة استئناف
     *   3 = محكمة ابتدائية تجارية
     *   4 = محكمة استئناف تجارية
     *   5 = محكمة ابتدائية إدارية
     *   6 = محكمة استئناف إدارية
     *   7 = محكمة النقض
     *
     * Degrés (id_degre):
     *   1 = الدرجة الأولى
     *   2 = الدرجة الثانية
     *   3 = محكمة النقض (درجة ثالثة)
     *
     */
    public function run(): void
    {
        $tribunaux = [
            // ============================================================
            // محاكم الابتدائية (88 محكمة) — Degré 1, Type 1
            // ============================================================
            // Ressort Cour d'appel de Rabat
            ['nom_tribunal' => 'المحكمة الابتدائية بالرباط',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 26],
            ['nom_tribunal' => 'المحكمة الابتدائية بتمارة',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 28],
            ['nom_tribunal' => 'المحكمة الابتدائية بسلا',                'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 27],
            ['nom_tribunal' => 'المحكمة الابتدائية بالخميسات',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 32],
            ['nom_tribunal' => 'المحكمة الابتدائية بتيفلت',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 84],
            ['nom_tribunal' => 'المحكمة الابتدائية بالرماني',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 85],
            // Ressort Cour d'appel de Kénitra
            ['nom_tribunal' => 'المحكمة الابتدائية بالقنيطرة',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 29],
            ['nom_tribunal' => 'المحكمة الابتدائية بسيدي قاسم',          'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 30],
            ['nom_tribunal' => 'المحكمة الابتدائية بسيدي سليمان',        'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 31],
            ['nom_tribunal' => 'المحكمة الابتدائية بمشرع بلقصيري',       'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 88],
            ['nom_tribunal' => 'المحكمة الابتدائية بسوق أربعاء الغرب',   'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 89],

            // Ressort Cour d'appel de Casablanca
            ['nom_tribunal' => 'المحكمة الابتدائية بالدار البيضاء',      'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 38],
            ['nom_tribunal' => 'المحكمة الابتدائية بالمحمدية',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 48],
            ['nom_tribunal' => 'المحكمة الابتدائية ببنسليمان',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 53],
            ['nom_tribunal' => 'المحكمة الابتدائية ببوزنيقة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 90],
            // Ressort Cour d'appel d'El Jadida
            ['nom_tribunal' => 'المحكمة الابتدائية بالجديدة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 47],
            ['nom_tribunal' => 'المحكمة الابتدائية بسيدي بنور',          'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 54],
            // Ressort Cour d'appel de Fès
            ['nom_tribunal' => 'المحكمة الابتدائية بفاس',                'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 17],
            ['nom_tribunal' => 'المحكمة الابتدائية بتاونات',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 24],
            ['nom_tribunal' => 'المحكمة الابتدائية بصفرو',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 22],
            ['nom_tribunal' => 'المحكمة الابتدائية ببولمان',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 23],
            ['nom_tribunal' => 'المحكمة الابتدائية بتازة',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 25],
            ['nom_tribunal' => 'المحكمة الابتدائية بجرسيف',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 16],
            // Ressort Cour d'appel de Marrakech
            ['nom_tribunal' => 'المحكمة الابتدائية بمراكش',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 55],
            ['nom_tribunal' => 'المحكمة الابتدائية بتحناوت',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 94],
            ['nom_tribunal' => 'المحكمة الابتدائية بشيشاوة',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 57],
            ['nom_tribunal' => 'المحكمة الابتدائية بإمنتانوت',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 95],
            ['nom_tribunal' => 'المحكمة الابتدائية بقلعة السراغنة',      'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 58],
            ['nom_tribunal' => 'المحكمة الابتدائية بابن جرير',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 96],
            // Ressort Cour d'appel d'Ouarzazate
            ['nom_tribunal' => 'المحكمة الابتدائية بورزازات',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 63],
            ['nom_tribunal' => 'المحكمة الابتدائية بزاكورة',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 64],
            ['nom_tribunal' => 'المحكمة الابتدائية بتنغير',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 65],
            // Ressort Cour d'appel de Safi
            ['nom_tribunal' => 'المحكمة الابتدائية بآسفي',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 60],
            ['nom_tribunal' => 'المحكمة الابتدائية باليوسفية',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 61],
            ['nom_tribunal' => 'المحكمة الابتدائية بالصويرة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 62],
            // Ressort Cour d'appel de Meknès
            ['nom_tribunal' => 'المحكمة الابتدائية بمكناس',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 18],
            ['nom_tribunal' => 'المحكمة الابتدائية بأزرو',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 97],
            ['nom_tribunal' => 'المحكمة الابتدائية بالحاجب',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 19],
            // Ressort Cour d'appel d'Errachidia
            ['nom_tribunal' => 'المحكمة الابتدائية بالرشيدية',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 66],
            ['nom_tribunal' => 'المحكمة الابتدائية بأرفود',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 98],
            ['nom_tribunal' => 'المحكمة الابتدائية بميدلت',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 67],
            ['nom_tribunal' => 'المحكمة الابتدائية بالريش',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 99],
            // Ressort Cour d'appel d'Agadir
            ['nom_tribunal' => 'المحكمة الابتدائية بأكادير',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 68],
            ['nom_tribunal' => 'المحكمة الابتدائية بإنزكان',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 69],
            ['nom_tribunal' => 'المحكمة الابتدائية ببيوكرى',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 100],
            ['nom_tribunal' => 'المحكمة الابتدائية بتارودانت',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 71],
            ['nom_tribunal' => 'المحكمة الابتدائية بتيزنيت',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 72],
            ['nom_tribunal' => 'المحكمة الابتدائية بطاطا',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 73],
            // Ressort Cour d'appel de Guelmim
            ['nom_tribunal' => 'المحكمة الابتدائية بكلميم',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 74],
            ['nom_tribunal' => 'المحكمة الابتدائية بطانطان',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 77],
            ['nom_tribunal' => 'المحكمة الابتدائية بأسا الزاك',          'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 75],
            ['nom_tribunal' => 'المحكمة الابتدائية بسيدي إفني',          'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 76],
            // Ressort Cour d'appel de Laâyoune
            ['nom_tribunal' => 'المحكمة الابتدائية بالعيون',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 78],
            ['nom_tribunal' => 'المحكمة الابتدائية ببوجدور',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 79],
            ['nom_tribunal' => 'المحكمة الابتدائية بالسمارة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 80],
            // Ressort Cour d'appel de Dakhla
            ['nom_tribunal' => 'المحكمة الابتدائية بالداخلة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 82],
            // Ressort Cour d'appel de Tanger
            ['nom_tribunal' => 'المحكمة الابتدائية بطنجة',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 1],
            ['nom_tribunal' => 'المحكمة الابتدائية بأصيلة',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 101],
            ['nom_tribunal' => 'المحكمة الابتدائية بالعرائش',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 5],
            ['nom_tribunal' => 'المحكمة الابتدائية بالقصر الكبير',       'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 102],
            // Ressort Cour d'appel de Tétouan
            ['nom_tribunal' => 'المحكمة الابتدائية بتطوان',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 3],
            ['nom_tribunal' => 'المحكمة الابتدائية بالمضيق',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 2],
            ['nom_tribunal' => 'المحكمة الابتدائية بشفشاون',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 7],
            ['nom_tribunal' => 'المحكمة الابتدائية بوزان',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 8],
            // Ressort Cour d'appel de Settat
            ['nom_tribunal' => 'المحكمة الابتدائية بسطات',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 52],
            ['nom_tribunal' => 'المحكمة الابتدائية ببن أحمد',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 103],
            ['nom_tribunal' => 'المحكمة الابتدائية ببرشيد',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 51],
            // Ressort Cour d'appel de Béni Mellal
            ['nom_tribunal' => 'المحكمة الابتدائية ببني ملال',           'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 33],
            ['nom_tribunal' => 'المحكمة الابتدائية بقصبة تادلة',         'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 104],
            ['nom_tribunal' => 'المحكمة الابتدائية بالفقيه بن صالح',     'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 35],
            ['nom_tribunal' => 'المحكمة الابتدائية بسوق السبت أولاد النمة','id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 105],
            ['nom_tribunal' => 'المحكمة الابتدائية بأزيلال',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 34],
            ['nom_tribunal' => 'المحكمة الابتدائية بدمنات',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 106],
            ['nom_tribunal' => 'المحكمة الابتدائية بخنيفرة',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 36],
            // Ressort Cour d'appel de Khouribga
            ['nom_tribunal' => 'المحكمة الابتدائية بخريبكة',             'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 37],
            ['nom_tribunal' => 'المحكمة الابتدائية بوادي زم',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 107],
            ['nom_tribunal' => 'المحكمة الابتدائية بأبي الجعد',          'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 108],
            // Ressort Cour d'appel d'Oujda
            ['nom_tribunal' => 'المحكمة الابتدائية بوجدة',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 9],
            ['nom_tribunal' => 'المحكمة الابتدائية ببركان',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 10],
            ['nom_tribunal' => 'المحكمة الابتدائية بتاوريرت',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 11],
            ['nom_tribunal' => 'المحكمة الابتدائية بجرادة',              'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 12],
            ['nom_tribunal' => 'المحكمة الابتدائية بفجيج',               'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 15],
            // Ressort Cour d'appel de Nador / Driouch
            ['nom_tribunal' => 'المحكمة الابتدائية بالناظور',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 13],
            ['nom_tribunal' => 'المحكمة الابتدائية بالدريوش',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 14],
            // Ressort Cour d'appel d'Al Hoceima
            ['nom_tribunal' => 'المحكمة الابتدائية بالحسيمة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 6],
            ['nom_tribunal' => 'المحكمة الابتدائية بتارجيست',            'id_type_tribunal' => 1, 'id_degre' => 1, 'id_province' => 109],

            // ============================================================
            // محاكم الاستئناف (22 محكمة) — Degré 2, Type 2
            // ============================================================
            ['nom_tribunal' => 'محكمة الاستئناف بالرباط',                'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 26],
            ['nom_tribunal' => 'محكمة الاستئناف بالقنيطرة',              'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 29],
            ['nom_tribunal' => 'محكمة الاستئناف بالدار البيضاء',         'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 39],
            ['nom_tribunal' => 'محكمة الاستئناف بالجديدة',               'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 47],
            ['nom_tribunal' => 'محكمة الاستئناف بفاس',                   'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 17],
            ['nom_tribunal' => 'محكمة الاستئناف بمراكش',                 'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 55],
            ['nom_tribunal' => 'محكمة الاستئناف بورزازات',               'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 63],
            ['nom_tribunal' => 'محكمة الاستئناف بآسفي',                  'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 60],
            ['nom_tribunal' => 'محكمة الاستئناف بمكناس',                 'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 18],
            ['nom_tribunal' => 'محكمة الاستئناف بالرشيدية',              'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 66],
            ['nom_tribunal' => 'محكمة الاستئناف بأكادير',                'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 68],
            ['nom_tribunal' => 'محكمة الاستئناف بكلميم',                 'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 74],
            ['nom_tribunal' => 'محكمة الاستئناف بالعيون',                'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 78],
            ['nom_tribunal' => 'محكمة الاستئناف بالداخلة',               'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 82],
            ['nom_tribunal' => 'محكمة الاستئناف بطنجة',                  'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 1],
            ['nom_tribunal' => 'محكمة الاستئناف بتطوان',                 'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 3],
            ['nom_tribunal' => 'محكمة الاستئناف بسطات',                  'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 52],
            ['nom_tribunal' => 'محكمة الاستئناف ببني ملال',              'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 33],
            ['nom_tribunal' => 'محكمة الاستئناف بخريبكة',                'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 37],
            ['nom_tribunal' => 'محكمة الاستئناف بوجدة',                  'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 9],
            ['nom_tribunal' => 'محكمة الاستئناف بالناظور',               'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 13],
            ['nom_tribunal' => 'محكمة الاستئناف بالحسيمة',               'id_type_tribunal' => 2, 'id_degre' => 2, 'id_province' => 6],

            // ============================================================
            // محاكم الابتدائية التجارية (10) — Degré 1, Type 3
            // ============================================================
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بالدار البيضاء', 'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 38],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بفاس',            'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 17],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بطنجة',           'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 1],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بمراكش',          'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 55],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بأكادير',         'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 68],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بالرباط',         'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 26],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بوجدة',           'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 9],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بالعيون',         'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 78],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية بالداخلة',        'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 82],
            ['nom_tribunal' => 'المحكمة الابتدائية التجارية ببني ملال',       'id_type_tribunal' => 3, 'id_degre' => 1, 'id_province' => 33],

            // ============================================================
            // محاكم الاستئناف التجارية (6) — Degré 2, Type 4
            // ============================================================
            ['nom_tribunal' => 'محكمة الاستئناف التجارية بالدار البيضاء',  'id_type_tribunal' => 4, 'id_degre' => 2, 'id_province' => 38],
            ['nom_tribunal' => 'محكمة الاستئناف التجارية بفاس',             'id_type_tribunal' => 4, 'id_degre' => 2, 'id_province' => 17],
            ['nom_tribunal' => 'محكمة الاستئناف التجارية بطنجة',            'id_type_tribunal' => 4, 'id_degre' => 2, 'id_province' => 1],
            ['nom_tribunal' => 'محكمة الاستئناف التجارية بمراكش',           'id_type_tribunal' => 4, 'id_degre' => 2, 'id_province' => 55],
            ['nom_tribunal' => 'محكمة الاستئناف التجارية بأكادير',          'id_type_tribunal' => 4, 'id_degre' => 2, 'id_province' => 68],
            ['nom_tribunal' => 'محكمة الاستئناف التجارية بالعيون',          'id_type_tribunal' => 4, 'id_degre' => 2, 'id_province' => 78],

            // ============================================================
            // محاكم الابتدائية الإدارية (10) — Degré 1, Type 5
            // ============================================================
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بالرباط',        'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 26],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بفاس',           'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 17],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بمراكش',         'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 55],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بأكادير',        'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 68],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بوجدة',          'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 9],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بالدار البيضاء', 'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 38],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بالعيون',        'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 78],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بالداخلة',       'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 82],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية بطنجة',          'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 1],
            ['nom_tribunal' => 'المحكمة الابتدائية الإدارية ببني ملال',      'id_type_tribunal' => 5, 'id_degre' => 1, 'id_province' => 33],

            // ============================================================
            // محاكم الاستئناف الإدارية (5) — Degré 2, Type 6
            // ============================================================
            ['nom_tribunal' => 'محكمة الاستئناف الإدارية بالرباط',          'id_type_tribunal' => 6, 'id_degre' => 2, 'id_province' => 26],
            ['nom_tribunal' => 'محكمة الاستئناف الإدارية بفاس',             'id_type_tribunal' => 6, 'id_degre' => 2, 'id_province' => 17],
            ['nom_tribunal' => 'محكمة الاستئناف الإدارية بمراكش',           'id_type_tribunal' => 6, 'id_degre' => 2, 'id_province' => 55],
            ['nom_tribunal' => 'محكمة الاستئناف الإدارية بأكادير',          'id_type_tribunal' => 6, 'id_degre' => 2, 'id_province' => 68],
            ['nom_tribunal' => 'محكمة الاستئناف الإدارية بطنجة',            'id_type_tribunal' => 6, 'id_degre' => 2, 'id_province' => 1],

            // ============================================================
            // محكمة النقض — Siège à Rabat, Type 7, Degré 3
            // ============================================================
            ['nom_tribunal' => 'محكمة النقض بالرباط',                       'id_type_tribunal' => 7, 'id_degre' => 3, 'id_province' => 26],
        ];

        DB::table('tribunaux')->insert($tribunaux);
    }
}