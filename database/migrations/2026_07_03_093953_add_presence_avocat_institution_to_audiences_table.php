<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audiences', function (Blueprint $table) {
            $table->boolean('presence_avocat_entraide')->default(false)->after('presence_defendeur');
        });
    }

    public function down(): void
    {
        Schema::table('audiences', function (Blueprint $table) {
            $table->dropColumn('presence_avocat_entraide');
        });
    }
};
