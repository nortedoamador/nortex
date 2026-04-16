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
        if (Schema::hasTable('financeiro_despesa_lancamentos')) {
            return;
        }

        Schema::create('financeiro_despesa_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->date('data_lancamento');
            $table->date('data_pagamento')->nullable();

            $table->string('descricao');
            $table->decimal('valor', 12, 2)->unsigned();

            $table->uuid('fixa_grupo_id')->nullable();

            $table->string('nota_path')->nullable();

            $table->timestamps();

            $table->index(['empresa_id', 'data_lancamento']);
            $table->index(['empresa_id', 'fixa_grupo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financeiro_despesa_lancamentos');
    }
};
