<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->string('cpi', 64)->nullable()->after('registro');
            $table->string('status', 32)->nullable()->after('cpi');
            $table->string('cidade', 120)->nullable()->after('status');
            $table->string('uf', 2)->nullable()->after('cidade');

            $table->string('nome_casco', 120)->nullable()->after('uf');
            $table->string('cor_casco', 80)->nullable()->after('nome_casco');

            $table->string('tipo', 80)->nullable()->after('cor_casco');
            $table->string('atividade', 80)->nullable()->after('tipo');
            $table->string('combustivel', 80)->nullable()->after('atividade');

            $table->unsignedSmallInteger('ano_fabricacao')->nullable()->after('combustivel');
            $table->decimal('comprimento_m', 8, 2)->nullable()->after('ano_fabricacao');
            $table->decimal('boca_m', 8, 2)->nullable()->after('comprimento_m');
            $table->decimal('pontal_m', 8, 2)->nullable()->after('boca_m');
            $table->decimal('tonelagem', 10, 2)->nullable()->after('pontal_m');

            $table->unsignedSmallInteger('passageiros')->nullable()->after('tonelagem');
            $table->unsignedSmallInteger('compartimentos')->nullable()->after('passageiros');

            $table->string('propulsao_motor', 120)->nullable()->after('compartimentos');
            $table->string('propulsao_leme', 120)->nullable()->after('propulsao_motor');

            $table->decimal('altura_proa_m', 8, 2)->nullable()->after('propulsao_leme');
            $table->decimal('altura_popa_m', 8, 2)->nullable()->after('altura_proa_m');

            $table->string('porto_cidade', 120)->nullable()->after('altura_popa_m');
            $table->string('porto_uf', 2)->nullable()->after('porto_cidade');

            $table->unsignedSmallInteger('refit_ano')->nullable()->after('porto_uf');
            $table->string('refit_local', 120)->nullable()->after('refit_ano');
            $table->string('responsavel_refit', 120)->nullable()->after('refit_local');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn([
                'cpi',
                'status',
                'cidade',
                'uf',
                'nome_casco',
                'cor_casco',
                'tipo',
                'atividade',
                'combustivel',
                'ano_fabricacao',
                'comprimento_m',
                'boca_m',
                'pontal_m',
                'tonelagem',
                'passageiros',
                'compartimentos',
                'propulsao_motor',
                'propulsao_leme',
                'altura_proa_m',
                'altura_popa_m',
                'porto_cidade',
                'porto_uf',
                'refit_ano',
                'refit_local',
                'responsavel_refit',
            ]);
        });
    }
};

