<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Le champ "observations" (مسطرة التنفيذ) était présent dans les formulaires
        // mais sans colonne ni $fillable : il n'était jamais enregistré.
        if (! Schema::hasColumn('executions', 'observations')) {
            Schema::table('executions', function (Blueprint $table) {
                $table->text('observations')->nullable()->after('date_execution');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('executions', 'observations')) {
            Schema::table('executions', function (Blueprint $table) {
                $table->dropColumn('observations');
            });
        }
    }
};
