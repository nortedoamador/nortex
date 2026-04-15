<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->string('inscricao_jurisdicao')->nullable()->after('inscricao_data_vencimento');
            $table->string('alienacao_fiduciaria', 8)->nullable()->after('inscricao_jurisdicao');
            $table->string('credor_hipotecario')->nullable()->after('alienacao_fiduciaria');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn(['inscricao_jurisdicao', 'alienacao_fiduciaria', 'credor_hipotecario']);
        });
    }
};
