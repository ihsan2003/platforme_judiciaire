<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tribunal;
use Illuminate\Support\Facades\Log;

class HierarchieTribunauxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Basé sur le Décret n° 2.23.665 du 10 novembre 2023 (Carte Judiciaire du Maroc).
     */
    public function run(): void
    {
        $this->seedDroitCommun();
        $this->seedCommercial();
        $this->seedAdministratif();
    }

    private function seedDroitCommun()
    {
        $hierarchy = [
            "الرباط" => ["الرباط", "تمارة", "سلا", "الخميسات", "تيفلت", "الرماني"],
            "القنيطرة" => ["القنيطرة", "سيدي قاسم", "مشرع بلقصيري", "سيدي سليمان", "سوق الأربعاء الغرب"],
            "الدار البيضاء" => ["الدار البيضاء", "المحمدية", "بنسليمان", "بوزنيقة"],
            "الجديدة" => ["الجديدة", "سيدي بنور"],
            "سطات" => ["سطات", "برشيد", "بن أحمد"],
            "فاس" => ["فاس", "صفرو", "بولمان", "تاونات"],
            "مكناس" => ["مكناس", "إفران", "الحاجب", "أزرو"],
            "تطوان" => ["تطوان", "شفشاون", "وزان", "المضيق"],
            "طنجة" => ["طنجة", "أصيلة", "العرائش", "القصر الكبير"],
            "وجدة" => ["وجدة", "بركان", "تاوريرت", "جرادة", "فجيج"],
            "الناظور" => ["الناظور", "الدريوش"],
            "الحسيمة" => ["الحسيمة", "تارجيست"],
            "بني ملال" => ["بني ملال", "قصبة تادلة", "أزيلال", "دمنات", "الفقيه بن صالح", "سوق السبت أولاد النمة", "خنيفرة"],
            "خريبكة" => ["خريبكة", "وادي زم", "أبي الجعد"],
            "مراكش" => ["مراكش", "تحناوت", "شيشاوة", "إمنتانوت", "قلعة السراغنة", "ابن جرير"],
            "آسفي" => ["آسفي", "اليوسفية", "الصويرة"],
            "ورزازات" => ["ورزازات", "زاكورة", "تنغير"],
            "أكادير" => ["أكادير", "إنزكان", "أيت ملول", "تارودانت", "تيزنيت", "طاطا", "بيوكرى"],
            "كلميم" => ["كلميم", "طانطان", "أسا الزاك", "سيدي إفني"],
            "العيون" => ["العيون", "السمارة", "بوجدور"],
            "الداخلة" => ["الداخلة"],
            "الرشيدية" => ["الرشيدية", "أرفود", "الريش", "ميدلت"],
            "تازة" => ["تازة", "كرسيف"]
        ];

        foreach ($hierarchy as $caName => $tpis) {
            $ca = Tribunal::where('nom_tribunal', 'LIKE', "%محكمة الإستئناف ب{$caName}%")
                ->where('id_degre', 2)
                ->first();

            if ($ca) {
                foreach ($tpis as $tpiName) {
                    Tribunal::where('nom_tribunal', 'LIKE', "%المحكمة الإبتدائية ب{$tpiName}%")
                        ->where('id_degre', 1)
                        ->update(['id_parent' => $ca->id]);
                }
            }
        }
    }

    private function seedCommercial()
    {
        $hierarchyCom = [
            "الدار البيضاء" => ["الدار البيضاء", "الرباط"],
            "فاس" => ["فاس", "وجدة", "طنجة"],
            "مراكش" => ["مراكش", "أكادير", "بني ملال"]
            // Note: Le décret 2023 prévoit des extensions (Tanger, Agadir) qui peuvent être ajoutées ici
        ];

        foreach ($hierarchyCom as $caName => $tpis) {
            $ca = Tribunal::where('nom_tribunal', 'LIKE', "%محكمة الإستئناف التجارية ب{$caName}%")->first();
            if ($ca) {
                foreach ($tpis as $tpiName) {
                    Tribunal::where('nom_tribunal', 'LIKE', "%المحكمة الإبتدائية التجارية ب{$tpiName}%")
                        ->update(['id_parent' => $ca->id]);
                }
            }
        }
    }

    private function seedAdministratif()
    {
        $hierarchyAdmin = [
            "الرباط" => ["الرباط", "القنيطرة"],
            "الدار البيضاء" => ["الدار البيضاء"],
            "فاس" => ["فاس", "وجدة", "مكناس"],
            "مراكش" => ["مراكش", "أكادير", "بني ملال"]
            // Note: Le décret 2023 prévoit aussi Tanger, Agadir, Laâyoune, Dakhla
        ];

        foreach ($hierarchyAdmin as $caName => $tpis) {
            $ca = Tribunal::where('nom_tribunal', 'LIKE', "%محكمة الإستئناف الإدارية ب{$caName}%")->first();
            if ($ca) {
                foreach ($tpis as $tpiName) {
                    Tribunal::where('nom_tribunal', 'LIKE', "%المحكمة الإبتدائية الإدارية ب{$tpiName}%")
                        ->update(['id_parent' => $ca->id]);
                }
            }
        }
    }
}
