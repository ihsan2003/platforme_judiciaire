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
     * NOTE: on référence les provinces par leur NOM (et non plus par ID en dur)
     * pour éviter les erreurs de clé étrangère si la table `provinces`
     * est re-seedée avec des IDs différents.
     */
    public function run(): void
    {
        // Mapping [nom_province => id] construit à partir de la table réelle
        $provinces = DB::table('provinces')->pluck('id', 'province');

        $tribunaux = [
            // ============================================================
            // محاكم الإبتدائية — Degré 1, Type 1
            // ============================================================
            // Ressort Cour d'appel de Rabat
            ['nom_tribunal' => 'المحكمة الإبتدائية بالرباط',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الرباط'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتمارة',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الصخيرات - تمارة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بسلا',                'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'سلا'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالخميسات',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الخميسات'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتيفلت',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تيفلت'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالرماني',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الرماني'],
            // Ressort Cour d'appel de Kénitra
            ['nom_tribunal' => 'المحكمة الإبتدائية بالقنيطرة',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'القنيطرة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بسيدي قاسم',          'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'سيدي قاسم'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بسيدي سليمان',        'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'سيدي سليمان'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بمشرع بلقصيري',       'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'مشرع بلقصيري'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بسوق أربعاء الغرب',   'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'سوق أربعاء الغرب'],

            // Ressort Cour d'appel de Casablanca
            ['nom_tribunal' => 'المحكمة الإبتدائية بالدار البيضاء',      'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الدار البيضاء'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالمحمدية',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'المحمدية'],
            ['nom_tribunal' => 'المحكمة الإبتدائية ببنسليمان',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'بنسليمان'],
            ['nom_tribunal' => 'المحكمة الإبتدائية ببوزنيقة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'بوزنيقة'],
            // Ressort Cour d'appel d'El Jadida
            ['nom_tribunal' => 'المحكمة الإبتدائية بالجديدة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الجديدة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بسيدي بنور',          'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'سيدي بنور'],
            // Ressort Cour d'appel de Fès
            ['nom_tribunal' => 'المحكمة الإبتدائية بفاس',                'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'فاس'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتاونات',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تاونات'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بصفرو',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'صفرو'],
            ['nom_tribunal' => 'المحكمة الإبتدائية ببولمان',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'بولمان'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتازة',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تازة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بجرسيف',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'كرسيف'],
            // Ressort Cour d'appel de Marrakech
            ['nom_tribunal' => 'المحكمة الإبتدائية بمراكش',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'مراكش'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتحناوت',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تحناوت'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بشيشاوة',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'شيشاوة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بإمنتانوت',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'إمنتانوت'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بقلعة السراغنة',      'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'قلعة السراغنة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بابن جرير',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'ابن جرير'],
            // Ressort Cour d'appel d'Ouarzazate
            ['nom_tribunal' => 'المحكمة الإبتدائية بورزازات',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'ورزازات'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بزاكورة',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'زاكورة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتنغير',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تنغير'],
            // Ressort Cour d'appel de Safi
            ['nom_tribunal' => 'المحكمة الإبتدائية بآسفي',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'آسفي'],
            ['nom_tribunal' => 'المحكمة الإبتدائية باليوسفية',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'اليوسفية'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالصويرة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الصويرة'],
            // Ressort Cour d'appel de Meknès
            ['nom_tribunal' => 'المحكمة الإبتدائية بمكناس',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'مكناس'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بأزرو',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'أزرو'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالحاجب',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الحاجب'],
            // Ressort Cour d'appel d'Errachidia
            ['nom_tribunal' => 'المحكمة الإبتدائية بالرشيدية',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الرشيدية'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بأرفود',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'أرفود'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بميدلت',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'ميدلت'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالريش',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الريش'],
            // Ressort Cour d'appel d'Agadir
            ['nom_tribunal' => 'المحكمة الإبتدائية بأكادير',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'أكادير'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بإنزكان',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'إنزكان - أيت ملول'],
            ['nom_tribunal' => 'المحكمة الإبتدائية ببيوكرى',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'بيوكرى'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتارودانت',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تارودانت'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتيزنيت',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تزنيت'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بطاطا',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'طاطا'],
            // Ressort Cour d'appel de Guelmim
            ['nom_tribunal' => 'المحكمة الإبتدائية بكلميم',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'كلميم'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بطانطان',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'طانطان'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بأسا الزاك',          'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'آسا - الزاك'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بسيدي إفني',          'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'سيدي إفني'],
            // Ressort Cour d'appel de Laâyoune
            ['nom_tribunal' => 'المحكمة الإبتدائية بالعيون',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'العيون'],
            ['nom_tribunal' => 'المحكمة الإبتدائية ببوجدور',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'بوجدور'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالسمارة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'السمارة'],
            // Ressort Cour d'appel de Dakhla
            ['nom_tribunal' => 'المحكمة الإبتدائية بالداخلة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الداخلة'],
            // Ressort Cour d'appel de Tanger
            ['nom_tribunal' => 'المحكمة الإبتدائية بطنجة',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'طنجة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بأصيلة',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'أصيلة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالعرائش',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'العرائش'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالقصر الكبير',       'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'القصر الكبير'],
            // Ressort Cour d'appel de Tétouan
            ['nom_tribunal' => 'المحكمة الإبتدائية بتطوان',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تطوان'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالمضيق',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'المضيق - الفنيدق'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بشفشاون',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'شفشاون'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بوزان',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'وزان'],
            // Ressort Cour d'appel de Settat
            ['nom_tribunal' => 'المحكمة الإبتدائية بسطات',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'سطات'],
            ['nom_tribunal' => 'المحكمة الإبتدائية ببن أحمد',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'بن أحمد'],
            ['nom_tribunal' => 'المحكمة الإبتدائية ببرشيد',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'برشيد'],
            // Ressort Cour d'appel de Béni Mellal
            ['nom_tribunal' => 'المحكمة الإبتدائية ببني ملال',           'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'بني ملال'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بقصبة تادلة',         'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'قصبة تادلة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالفقيه بن صالح',     'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الفقيه بن صالح'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بسوق السبت أولاد النمة','id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'سوق السبت أولاد النمة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بأزيلال',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'أزيلال'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بدمنات',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'دمنات'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بخنيفرة',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'خنيفرة'],
            // Ressort Cour d'appel de Khouribga
            ['nom_tribunal' => 'المحكمة الإبتدائية بخريبكة',             'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'خريبكة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بوادي زم',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'وادي زم'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بأبي الجعد',          'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'أبي الجعد'],
            // Ressort Cour d'appel d'Oujda
            ['nom_tribunal' => 'المحكمة الإبتدائية بوجدة',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'وجدة - أنكاد'],
            ['nom_tribunal' => 'المحكمة الإبتدائية ببركان',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'بركان'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتاوريرت',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تاوريرت'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بجرادة',              'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'جرادة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بفجيج',               'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'فكيك'],
            // Ressort Cour d'appel de Nador / Driouch
            ['nom_tribunal' => 'المحكمة الإبتدائية بالناظور',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الناظور'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بالدريوش',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'دريوش'],
            // Ressort Cour d'appel d'Al Hoceima
            ['nom_tribunal' => 'المحكمة الإبتدائية بالحسيمة',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'الحسيمة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية بتارجيست',            'id_type_tribunal' => 1, 'id_degre' => 1, 'province' => 'تارجيست'],

            // ============================================================
            // محاكم الإستئناف — Degré 2, Type 2
            // ============================================================
            ['nom_tribunal' => 'محكمة الإستئناف بالرباط',                'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'الرباط'],
            ['nom_tribunal' => 'محكمة الإستئناف بالقنيطرة',              'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'القنيطرة'],
            // NOTE: l'ancien seeder utilisait id_province = 39, qui correspond
            // à "أنفا" (et non "الدار البيضاء" = 38) dans la table provinces.
            // Vérifie si c'était voulu ; sinon remplace par 'الدار البيضاء'.
            ['nom_tribunal' => 'محكمة الإستئناف بالدار البيضاء',         'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'أنفا'],
            ['nom_tribunal' => 'محكمة الإستئناف بالجديدة',               'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'الجديدة'],
            ['nom_tribunal' => 'محكمة الإستئناف بفاس',                   'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'فاس'],
            ['nom_tribunal' => 'محكمة الإستئناف بمراكش',                 'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'مراكش'],
            ['nom_tribunal' => 'محكمة الإستئناف بورزازات',               'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'ورزازات'],
            ['nom_tribunal' => 'محكمة الإستئناف بآسفي',                  'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'آسفي'],
            ['nom_tribunal' => 'محكمة الإستئناف بمكناس',                 'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'مكناس'],
            ['nom_tribunal' => 'محكمة الإستئناف بالرشيدية',              'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'الرشيدية'],
            ['nom_tribunal' => 'محكمة الإستئناف بأكادير',                'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'أكادير'],
            ['nom_tribunal' => 'محكمة الإستئناف بكلميم',                 'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'كلميم'],
            ['nom_tribunal' => 'محكمة الإستئناف بالعيون',                'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'العيون'],
            ['nom_tribunal' => 'محكمة الإستئناف بالداخلة',               'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'الداخلة'],
            ['nom_tribunal' => 'محكمة الإستئناف بطنجة',                  'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'طنجة'],
            ['nom_tribunal' => 'محكمة الإستئناف بتطوان',                 'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'تطوان'],
            ['nom_tribunal' => 'محكمة الإستئناف بسطات',                  'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'سطات'],
            ['nom_tribunal' => 'محكمة الإستئناف ببني ملال',              'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'بني ملال'],
            ['nom_tribunal' => 'محكمة الإستئناف بخريبكة',                'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'خريبكة'],
            ['nom_tribunal' => 'محكمة الإستئناف بوجدة',                  'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'وجدة - أنكاد'],
            ['nom_tribunal' => 'محكمة الإستئناف بالناظور',               'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'الناظور'],
            ['nom_tribunal' => 'محكمة الإستئناف بالحسيمة',               'id_type_tribunal' => 2, 'id_degre' => 2, 'province' => 'الحسيمة'],

            // ============================================================
            // محاكم الإبتدائية التجارية — Degré 1, Type 3
            // ============================================================
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بالدار البيضاء', 'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'الدار البيضاء'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بفاس',            'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'فاس'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بطنجة',           'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'طنجة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بمراكش',          'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'مراكش'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بأكادير',         'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'أكادير'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بالرباط',         'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'الرباط'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بوجدة',           'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'وجدة - أنكاد'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بالعيون',         'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'العيون'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية بالداخلة',        'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'الداخلة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية التجارية ببني ملال',       'id_type_tribunal' => 3, 'id_degre' => 1, 'province' => 'بني ملال'],

            // ============================================================
            // محاكم الإستئناف التجارية — Degré 2, Type 4
            // ============================================================
            ['nom_tribunal' => 'محكمة الإستئناف التجارية بالدار البيضاء',  'id_type_tribunal' => 4, 'id_degre' => 2, 'province' => 'الدار البيضاء'],
            ['nom_tribunal' => 'محكمة الإستئناف التجارية بفاس',             'id_type_tribunal' => 4, 'id_degre' => 2, 'province' => 'فاس'],
            ['nom_tribunal' => 'محكمة الإستئناف التجارية بطنجة',            'id_type_tribunal' => 4, 'id_degre' => 2, 'province' => 'طنجة'],
            ['nom_tribunal' => 'محكمة الإستئناف التجارية بمراكش',           'id_type_tribunal' => 4, 'id_degre' => 2, 'province' => 'مراكش'],
            ['nom_tribunal' => 'محكمة الإستئناف التجارية بأكادير',          'id_type_tribunal' => 4, 'id_degre' => 2, 'province' => 'أكادير'],
            ['nom_tribunal' => 'محكمة الإستئناف التجارية بالعيون',          'id_type_tribunal' => 4, 'id_degre' => 2, 'province' => 'العيون'],

            // ============================================================
            // محاكم الإبتدائية الإدارية — Degré 1, Type 5
            // ============================================================
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بالرباط',        'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'الرباط'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بفاس',           'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'فاس'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بمراكش',         'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'مراكش'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بأكادير',        'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'أكادير'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بوجدة',          'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'وجدة - أنكاد'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بالدار البيضاء', 'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'الدار البيضاء'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بالعيون',        'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'العيون'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بالداخلة',       'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'الداخلة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية بطنجة',          'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'طنجة'],
            ['nom_tribunal' => 'المحكمة الإبتدائية الإدارية ببني ملال',      'id_type_tribunal' => 5, 'id_degre' => 1, 'province' => 'بني ملال'],

            // ============================================================
            // محاكم الإستئناف الإدارية — Degré 2, Type 6
            // ============================================================
            ['nom_tribunal' => 'محكمة الإستئناف الإدارية بالرباط',          'id_type_tribunal' => 6, 'id_degre' => 2, 'province' => 'الرباط'],
            ['nom_tribunal' => 'محكمة الإستئناف الإدارية بفاس',             'id_type_tribunal' => 6, 'id_degre' => 2, 'province' => 'فاس'],
            ['nom_tribunal' => 'محكمة الإستئناف الإدارية بمراكش',           'id_type_tribunal' => 6, 'id_degre' => 2, 'province' => 'مراكش'],
            ['nom_tribunal' => 'محكمة الإستئناف الإدارية بأكادير',          'id_type_tribunal' => 6, 'id_degre' => 2, 'province' => 'أكادير'],
            ['nom_tribunal' => 'محكمة الإستئناف الإدارية بطنجة',            'id_type_tribunal' => 6, 'id_degre' => 2, 'province' => 'طنجة'],

            // ============================================================
            // محكمة النقض — Siège à Rabat, Type 7, Degré 3
            // ============================================================
            ['nom_tribunal' => 'محكمة النقض بالرباط',                       'id_type_tribunal' => 7, 'id_degre' => 3, 'province' => 'الرباط'],
        ];

        $data = collect($tribunaux)->map(function ($t) use ($provinces) {
            if (!isset($provinces[$t['province']])) {
                throw new \Exception("Province introuvable dans la table `provinces`: {$t['province']} (tribunal: {$t['nom_tribunal']})");
            }

            return [
                'nom_tribunal'     => $t['nom_tribunal'],
                'id_type_tribunal' => $t['id_type_tribunal'],
                'id_degre'         => $t['id_degre'],
                'id_province'      => $provinces[$t['province']],
            ];
        })->toArray();

        DB::table('tribunaux')->insert($data);
    }
}