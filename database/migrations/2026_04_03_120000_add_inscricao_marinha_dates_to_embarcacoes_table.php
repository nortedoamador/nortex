<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->date('inscricao_data_emissao')->nullable()->after('inscricao');
            $table->date('inscricao_data_vencimento')->nullable()->after('inscricao_data_emissao');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn(['inscricao_data_emissao', 'inscricao_data_vencimento']);
        });
    }
};
