<?php
// database/seeders/DataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class DataSeeder extends Seeder
{
    public function run()
    {
        // أنواع القضايا
        $typesAffaire = [
            [
                'affaire' => 'مدني',
                'code' => '1201',
            ],
            [
                'affaire' => 'إداري',
                'code' => '1204',
            ],
            [
                'affaire' => 'مدني اجتماعي',
                'code' => '1501',
            ],
            [
                'affaire' => 'تجاري',
                'code' => '1203',
            ],
            [
                'affaire' => 'حوادث الشغل',
                'code' => '1502',
            ],
            [
                'affaire' => 'نزاعات الشغل',
                'code' => '1501',
            ],
            [
                'affaire' => 'متنوع',
                'code' => '1201',
            ],
            [
                'affaire' => 'الصفقات العمومية',
                'code' => '7102',
            ],
            [
                'affaire' => 'استعجالي',
                'code' => '1101',
            ],
            [
                'affaire' => 'عقاري',
                'code' => '1401',
            ],
            [
                'affaire' => 'جرائم الأموال',
                'code' => '2105',
            ],
            [
                'affaire' => 'مدني عقاري',
                'code' => '0000',
            ],
            [
                'affaire' => 'جنحي',
                'code' => '0000',
            ],
            [
                'affaire' => 'حوادث السير',
                'code' => '0000',
            ],
        ];

        foreach ($typesAffaire as $type) {
            DB::table('type_affaires')->updateOrInsert([
                'affaire' => $type['affaire'],
                'code' => $type['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // حالات الملف
        $statuts = [
            'جاري',
            'في طور الاستئناف',
            'في طور النقض',
            'في طور التعرض',
            'في طور إعادة النظر',
            'تم الحكم',
            'تم التنفيذ',
            'قيد التنفيذ',
            'حفظ'
        ];
        foreach ($statuts as $statut) {
            DB::table('statut_dossiers')->updateOrInsert(['statut_dossier' => $statut]);
        }

        // أنواع الأطراف
        $typesPartie = [
            'المدعي',
           'المدعى عليه',
            'متدخل'
        ];
        foreach ($typesPartie as $type) {
            DB::table('type_parties')->updateOrInsert(['type_partie' => $type]);
        }

        // أنواع المحاكم
        $typesTribunal = [
            'المحكمة الابتدائية',
            'محكمة الإستئناف',  
            'المحكمة التجارية',
            'محكمة الإستئناف التجارية',     
            'المحكمة الإدارية',
            'محكمة الإستئناف الإدارية',
            'محكمة النقض',
            'المحكمة المدنية',
            'المحكمة الإجتماعية',
        ];
        foreach ($typesTribunal as $type) {
            DB::table('type_tribunaux')->updateOrInsert(['tribunal' => $type]);
        }

        // درجات التقاضي
        $degres = [
            'الدرجة الأولى',
            'الإستئناف',
            'النقض',
            'التعرض',
            'إعادة النظر'
        ];
        foreach ($degres as $degre) {
            DB::table('degre_juridictions')->updateOrInsert(['degre_juridiction' => $degre]);
        }

        // أنواع الجلسات
        $typesAudience = [
            'التحقيق',
            'المرافعة',
            'الإعداد',
            'الحكم',
            'البحث',
            'الخبرة',
            'المداولة',
            'الإخراج من المداولة',
            'آخر',
        ];
        foreach ($typesAudience as $type) {
            DB::table('type_audiences')->updateOrInsert(['type_audience' => $type]);
        }

        // مواقف الحكم بالنسبة للمؤسسة
        $positions = [
            'مع',
            'ضد',
        ];
        foreach ($positions as $position) {
            DB::table('position_institutions')->updateOrInsert(['position' => $position]);
        }

        // أنواع الطعون مع الآجال القانونية
        $recours = [
            ['type_recours' => 'استئناف', 'delai_legal_jours' => 30],
            ['type_recours' => 'تعرض', 'delai_legal_jours' => 10],
            ['type_recours' => 'الطعن بالنقض', 'delai_legal_jours' => 30],
            ['type_recours' => 'إعادة النظر', 'delai_legal_jours' => 10],
            ['type_recours' => 'نقض وإحالة', 'delai_legal_jours' => 30], 
            ['type_recours' => 'نقض مع رفض الطعن', 'delai_legal_jours' => 30],
        ];
        foreach ($recours as $r) {
            DB::table('type_recours')->updateOrInsert($r);
        }

        // حالات التنفيذ
        $statutsExec = [
            'قيد التنفيذ',
            'تنفيذ جزئي',
            'تنفيذ كامل',
        ];
        foreach ($statutsExec as $statut) {
            DB::table('statut_executions')->updateOrInsert(['statut_execution' => $statut]);
        }

        // أنواع الوثائق
        $typesDoc = [
            'استدعاء',
            'طلب',
            'حكم',
            'محضر خبرة',
            'عقد',
            'وثيقة',
            'حكم تمهيدي',
            'مقال إفتتاحي',
            'مذكرة جوابية',
            'مذكرة تعقيبية'
        ];
        foreach ($typesDoc as $type) {
            DB::table('type_documents')->updateOrInsert(['type_document' => $type]);
        }

        // حالات الشكايات
        $statutsRecl = [
            'قيد المعالجة',
            'تمت المعالجة',
            'مغلقة'
        ];
        foreach ($statutsRecl as $statut) {
            DB::table('statut_reclamations')->updateOrInsert(['statut_reclamation' => $statut]);
        }

        // أنواع المشتكين
        $typesReclamant = [
            'شركة',
            'مقاولة',
            'مرفق عمومي',
            'مستخدم',
            'مرتفق',
            'مؤسسة الوسيط',
            'مديرية جهوية',
            'مديرية إقليمية',
            'آخر'
        ];
        foreach ($typesReclamant as $type) {
            DB::table('type_reclamants')->updateOrInsert(['type_reclamant' => $type]);
        }

        // أنواع الإجراءات
        $typesAction = [
            'رد',
            'إحالة'
        ];
        foreach ($typesAction as $type) {
            DB::table('type_actions')->updateOrInsert(['type_action' => $type]);
        }

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Création des permissions
        $permissions = [
            // Permissions pour les dossiers
            'view dossiers',
            'create dossiers',
            'edit dossiers',
            'delete dossiers',
            
            // Permissions pour les réclamations
            'view reclamations',
            'create reclamations',
            'edit reclamations',
            'delete reclamations',
            
            // Permissions pour la gestion des utilisateurs
            'manage users',
            
            // Permissions supplémentaires
            'view statistiques',
            'export data',
            'manage notifications'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Création des rôles
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleUser = Role::firstOrCreate(['name' => 'utilisateur', 'guard_name' => 'web']);

        // Assignation des permissions aux rôles
        $roleAdmin->givePermissionTo(Permission::all());
        
        $roleUser->givePermissionTo([
            'view dossiers',
            'edit dossiers',
            'view reclamations',
            'view statistiques'
        ]);

    }
}