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
            'مدني',
            'إداري',
            'مدني اجتماعي',
            'تجاري',
            'حوادث الشغل',
            'نزاعات الشغل',
            'متنوع',
            'الصفقات العمومية',
            'استعجالي',
            'عقاري'
        ];
        foreach ($typesAffaire as $type) {
            DB::table('type_affaires')->insert(['affaire' => $type]);
        }

        // حالات الملف
        $statuts = [
            'جاري',
            'تم الحكم',
            'تم التنفيذ',
            'مغلق',
            'موقوف',
            'في طور الاستئناف',
            'في طور النقض',
            'في طور التعرض',
            'في طور إعادة النظر',
            'إعادة فتح',
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
            'محكمة الاستئناف',
            'محكمة النقض',
            'المحكمة الإدارية',
            'محكمة الاستئناف الإدارية',
            'المحكمة التجارية',
            'محكمة الاستئناف التجارية'
        ];
        foreach ($typesTribunal as $type) {
            DB::table('type_tribunaux')->insert(['tribunal' => $type]);
        }

        // درجات التقاضي
        $degres = [
            'الدرجة الأولى',
            'الإستئناف',
            'النقض'
        ];
        foreach ($degres as $degre) {
            DB::table('degre_juridictions')->insert(['degre_juridiction' => $degre]);
        }

        // أنواع الجلسات
        $typesAudience = [
            'التحقيق',
            'المرافعة',
            'الإعداد',
            'الحكم'
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
            'في الانتظار',
            'قيد التنفيذ',
            'تنفيذ جزئي',
            'تنفيذ كامل',
            'متروك'
        ];
        foreach ($statutsExec as $statut) {
            DB::table('statut_executions')->insert(['statut_execution' => $statut]);
        }

        // أنواع الوثائق
        $typesDoc = [
            'استدعاء',
            'طلب',
            'حكم',
            'محضر',
            'عقد',
            'وثيقة'
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
            'مباشر',
            'مؤسسة',
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