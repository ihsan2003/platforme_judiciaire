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
        ];

        foreach ($typesAffaire as $type) {
            DB::table('type_affaires')->insert([
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
            'حفظ'
        ];
        foreach ($statuts as $statut) {
            DB::table('statut_dossiers')->insert(['statut_dossier' => $statut]);
        }

        // أنواع الأطراف
        $typesPartie = [
            'مدعي',
           'مدعى عليه',
            'متدخل'
        ];
        foreach ($typesPartie as $type) {
            DB::table('type_parties')->insert(['type_partie' => $type]);
        }

        // أنواع المحاكم
        $typesTribunal = [
            'المحكمة الابتدائية',
            'محكمة الإستئناف',
            'محكمة النقض',
            'المحكمة الإدارية',
            'محكمة الإستئناف الإدارية',
            'المحكمة التجارية',
            'محكمة الإستئناف التجارية'
        ];
        foreach ($typesTribunal as $type) {
            DB::table('type_tribunaux')->insert(['tribunal' => $type]);
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
            DB::table('degre_juridictions')->insert(['degre_juridiction' => $degre]);
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
        ];
        foreach ($typesAudience as $type) {
            DB::table('type_audiences')->insert(['type_audience' => $type]);
        }

        // مواقف الحكم بالنسبة للمؤسسة
        $positions = [
            'مع',
            'ضد',
            'جزئي'
        ];
        foreach ($positions as $position) {
            DB::table('position_institutions')->insert(['position' => $position]);
        }

        // أنواع الطعون مع الآجال القانونية
        $recours = [
            ['type_recours' => 'استئناف', 'delai_legal_jours' => 30],
            ['type_recours' => 'تعرض', 'delai_legal_jours' => 15],
            ['type_recours' => 'الطعن بالنقض', 'delai_legal_jours' => 30],
            ['type_recours' => 'إعادة النظر', 'delai_legal_jours' => 30],
        ];
        foreach ($recours as $r) {
            DB::table('type_recours')->insert($r);
        }

        // حالات التنفيذ
        $statutsExec = [
            'قيد التنفيذ',
            'تنفيذ جزئي',
            'تنفيذ كامل',
        ];
        foreach ($statutsExec as $statut) {
            DB::table('statut_executions')->insert(['statut_execution' => $statut]);
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
            DB::table('type_documents')->insert(['type_document' => $type]);
        }

        // حالات الشكايات
        $statutsRecl = [
            'قيد المعالجة',
            'تمت المعالجة',
            'مغلقة'
        ];
        foreach ($statutsRecl as $statut) {
            DB::table('statut_reclamations')->insert(['statut_reclamation' => $statut]);
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
            DB::table('type_reclamants')->insert(['type_reclamant' => $type]);
        }

        // أنواع الإجراءات
        $typesAction = [
            'رد',
            'إحالة'
        ];
        foreach ($typesAction as $type) {
            DB::table('type_actions')->insert(['type_action' => $type]);
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
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Création des rôles
        $roleAdmin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $roleUser = Role::create(['name' => 'utilisateur', 'guard_name' => 'web']);

        // Assignation des permissions aux rôles
        $roleAdmin->givePermissionTo(Permission::all());
        
        $roleUser->givePermissionTo([
            'view dossiers',
            'view reclamations',
            'view statistiques'
        ]);

        // Création d'un utilisateur admin
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@entraide.ma',
            'password' => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $admin->assignRole('admin');

        // Création d'un utilisateur standard
        $user = User::create([
            'name' => 'Utilisateur Standard',
            'email' => 'user@entraide.ma',
            'password' => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $user->assignRole('utilisateur');
    }
}