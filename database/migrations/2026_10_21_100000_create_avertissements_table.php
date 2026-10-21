<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avertissements', function (Blueprint $table) {
            $table->id();

            // الحكم موضوع النزاع
            $table->text('decision_objet')->nullable();
            // نوعية الملف
            $table->foreignId('id_type_affaire')->constrained('type_affaires');
            // المحكمة المختصة
            $table->foreignId('id_tribunal')->constrained('tribunaux');
            // المدينة
            $table->foreignId('id_province')->nullable()->constrained('provinces')->nullOnDelete();
            // الإجراء المتخذ من طرف مصلحة المنازعات والشؤون القانونية
            $table->text('action_service')->nullable();
            // المدعي / المدعى عليه
            $table->foreignId('id_demandeur')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('id_defendeur')->nullable()->constrained('parties')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avertissements');
    }
};
