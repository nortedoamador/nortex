<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('nome_fantasia', 255)->nullable()->after('nome');
            $table->string('inscricao_estadual', 32)->nullable()->after('cnpj');
            $table->string('cep', 12)->nullable()->after('cidade');
            $table->string('endereco', 255)->nullable()->after('cep');
            $table->string('numero', 32)->nullable()->after('endereco');
            $table->string('complemento', 120)->nullable()->after('numero');
            $table->string('bairro', 120)->nullable()->after('complemento');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'nome_fantasia',
                'inscricao_estadual',
                'cep',
                'endereco',
                'numero',
                'complemento',
                'bairro',
            ]);
        });
    }
};
