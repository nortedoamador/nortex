<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->date('data_nascimento')->nullable()->after('cpf');
            $table->string('nome_pai', 255)->nullable()->after('naturalidade');
            $table->string('nome_mae', 255)->nullable()->after('nome_pai');
            $table->string('numero_cnh', 32)->nullable()->after('documento_identidade_numero');
            $table->string('categoria_cnh', 16)->nullable()->after('numero_cnh');
            $table->date('validade_cnh')->nullable()->after('categoria_cnh');
            $table->date('primeira_habilitacao')->nullable()->after('validade_cnh');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'data_nascimento',
                'nome_pai',
                'nome_mae',
                'numero_cnh',
                'categoria_cnh',
                'validade_cnh',
                'primeira_habilitacao',
            ]);
        });
    }
};
