<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aulas_nauticas', function (Blueprint $table) {
            $table->string('tipo_aula', 30)->default('teorica')->after('local');
        });
    }

    public function down(): void
    {
        Schema::table('aulas_nauticas', function (Blueprint $table) {
            $table->dropColumn('tipo_aula');
        });
    }
};

