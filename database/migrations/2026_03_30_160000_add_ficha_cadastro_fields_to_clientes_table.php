<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('rg', 32)->nullable()->after('cpf');
            $table->string('orgao_emissor', 32)->nullable()->after('rg');
            $table->date('data_emissao_rg')->nullable()->after('orgao_emissor');
            $table->string('nacionalidade', 100)->nullable()->after('data_emissao_rg');
            $table->string('naturalidade', 100)->nullable()->after('nacionalidade');
            $table->string('cep', 12)->nullable()->after('naturalidade');
            $table->string('endereco', 255)->nullable()->after('cep');
            $table->string('bairro', 120)->nullable()->after('endereco');
            $table->string('cidade', 120)->nullable()->after('bairro');
            $table->string('uf', 2)->nullable()->after('cidade');
            $table->string('numero', 20)->nullable()->after('uf');
            $table->string('complemento', 120)->nullable()->after('numero');
            $table->string('apartamento', 50)->nullable()->after('complemento');
            $table->string('celular', 32)->nullable()->after('telefone');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'rg',
                'orgao_emissor',
                'data_emissao_rg',
                'nacionalidade',
                'naturalidade',
                'cep',
                'endereco',
                'bairro',
                'cidade',
                'uf',
                'numero',
                'complemento',
                'apartamento',
                'celular',
            ]);
        });
    }
};
