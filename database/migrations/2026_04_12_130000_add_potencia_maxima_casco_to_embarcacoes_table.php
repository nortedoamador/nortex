<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->string('potencia_maxima_casco', 120)->nullable()->after('numero_casco');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn('potencia_maxima_casco');
        });
    }
};
