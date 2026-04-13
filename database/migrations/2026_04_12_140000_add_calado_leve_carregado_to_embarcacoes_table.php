<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->string('calado_leve', 64)->nullable()->after('contorno');
            $table->string('calado_carregado', 64)->nullable()->after('calado_leve');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn(['calado_leve', 'calado_carregado']);
        });
    }
};
