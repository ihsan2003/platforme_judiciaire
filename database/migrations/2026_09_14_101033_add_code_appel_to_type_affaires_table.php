<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Ajoute le code utilisé pour générer le numéro de dossier au degré
 * d'appel (رمز الفئة بالنسبة لدرجة الاستئناف), en complément de la
 * colonne "code" existante qui sert uniquement au degré ابتدائي.
 *
 * Référence : رموز الملفات بالمحاكم المغربية
 * https://mandili.net/law/25925
 *
 * Remarque : contrairement au code "ابتدائي", il n'existe pas de formule
 * unique pour dériver le code d'appel (dans certaines catégories civiles
 * c'est souvent code+50, mais pas dans les catégories jنحي/إداري). On ne
 * préremplit donc ici que les correspondances confirmées par la source
 * ci-dessus ; le reste doit être complété manuellement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('type_affaires', function (Blueprint $table) {
            $table->string('code_appel', 20)->nullable()->after('code');
        });

        // Correspondances ابتدائي → استئنافي confirmées sur mandili.net/law/25925
        $mapping = [
            '1201' => '1251', // المدني المتنوع → المدني المتنوع المستأنف
            '1203' => '1253', // التجاري → التجاري المستأنف
            '1204' => '1254', // الإداري → الإداري المستأنف
            '1401' => '1451', // العقار العادي → العقار العادي المستأنف
            '1501' => '1551', // نزاعات الشغل → نزاعات الشغل المستأنفة
            '1502' => '1552', // حوادث الشغل → حوادث الشغل المستأنفة
            '1101' => '1221', // الاستعجالي → القضايا الاستعجالية المستأنفة
        ];

        foreach ($mapping as $codeAbtidai => $codeAppel) {
            DB::table('type_affaires')
                ->where('code', $codeAbtidai)
                ->update(['code_appel' => $codeAppel]);
        }

        // Types restants (ex: 7102 الصفقات العمومية, 2105 جرائم الأموال) :
        // aucune correspondance fiable trouvée dans la source, à compléter
        // manuellement via la page نوع القضايا / type_affaires.
    }

    public function down(): void
    {
        Schema::table('type_affaires', function (Blueprint $table) {
            $table->dropColumn('code_appel');
        });
    }
};