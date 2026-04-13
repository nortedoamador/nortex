<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            // Campos conforme ficha (print)
            $table->string('cpf', 20)->nullable()->after('cliente_id');
            $table->string('inscricao', 128)->nullable()->after('cpf');

            $table->string('pontal', 64)->nullable()->after('inscricao');
            $table->string('calado', 64)->nullable()->after('pontal');
            $table->string('contorno', 64)->nullable()->after('calado');

            $table->string('material_casco', 120)->nullable()->after('contorno');
            $table->string('numero_casco', 120)->nullable()->after('material_casco');
            $table->string('cor_casco_ficha', 120)->nullable()->after('numero_casco');

            $table->string('construtor', 120)->nullable()->after('cor_casco_ficha');
            $table->unsignedSmallInteger('ano_construcao')->nullable()->after('construtor');
            $table->unsignedSmallInteger('tripulantes')->nullable()->after('ano_construcao');

            $table->string('comprimento', 64)->nullable()->after('tripulantes');
            $table->string('boca', 64)->nullable()->after('comprimento');

            $table->string('arqueacao_bruta', 64)->nullable()->after('boca');
            $table->string('arqueacao_liquida', 64)->nullable()->after('arqueacao_bruta');

            $table->string('marca_motor', 120)->nullable()->after('arqueacao_liquida');
            $table->string('potencia_maxima_motor', 120)->nullable()->after('marca_motor');
            $table->string('numero_motor', 120)->nullable()->after('potencia_maxima_motor');

            // Dados da Nota Fiscal
            $table->string('nf_numero', 64)->nullable()->after('numero_motor');
            $table->date('nf_data')->nullable()->after('nf_numero');
            $table->string('nf_vendedor', 120)->nullable()->after('nf_data');
            $table->string('nf_local', 120)->nullable()->after('nf_vendedor');
            $table->string('nf_documento_vendedor', 40)->nullable()->after('nf_local');
        });
    }

    public function down(): void
    {
        Schema::table('embarcacoes', function (Blueprint $table) {
            $table->dropColumn([
                'cpf',
                'inscricao',
                'pontal',
                'calado',
                'contorno',
                'material_casco',
                'numero_casco',
                'cor_casco_ficha',
                'construtor',
                'ano_construcao',
                'tripulantes',
                'comprimento',
                'boca',
                'arqueacao_bruta',
                'arqueacao_liquida',
                'marca_motor',
                'potencia_maxima_motor',
                'numero_motor',
                'nf_numero',
                'nf_data',
                'nf_vendedor',
                'nf_local',
                'nf_documento_vendedor',
            ]);
        });
    }
};

