<?php
// database/migrations/2026_07_17_000000_create_type_reclamations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('type_reclamations', function (Blueprint $table) {
            $table->id();
            $table->string('type_reclamation')->unique();
            $table->timestamps();
        });

        // أنواع الشكايات (قيم ثابتة)
        $types = [
            'شكايات مرتبطة بالمنازعات القضائية',
            'شكايات الموارد البشرية',
            'شكايات المرتفقين',
            'شكايات أخرى',
        ];

        foreach ($types as $type) {
            DB::table('type_reclamations')->insert([
                'type_reclamation' => $type,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('type_reclamations');
    }
};