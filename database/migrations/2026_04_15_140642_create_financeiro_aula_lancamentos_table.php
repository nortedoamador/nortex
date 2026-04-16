<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financeiro_aula_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->date('data_lancamento');
            $table->date('data_pagamento')->nullable();

            $table->unsignedInteger('qtd_alunos')->default(0);

            $table->decimal('receita', 12, 2)->unsigned();

            $table->decimal('custo_barco', 12, 2)->unsigned()->default(0);
            $table->decimal('custo_combustivel', 12, 2)->unsigned()->default(0);
            $table->decimal('custo_cafe', 12, 2)->unsigned()->default(0);
            $table->decimal('custo_ingresso', 12, 2)->unsigned()->default(0);
            $table->decimal('taxa_marinha', 12, 2)->unsigned()->default(0);

            $table->decimal('custo_total', 12, 2)->unsigned();
            $table->decimal('lucro', 12, 2);

            $table->timestamps();

            $table->index(['empresa_id', 'data_lancamento']);
            $table->index(['empresa_id', 'data_pagamento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financeiro_aula_lancamentos');
    }
};
