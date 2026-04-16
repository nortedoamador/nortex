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
        if (Schema::hasTable('financeiro_lote_engenharia_items')) {
            return;
        }

        Schema::create('financeiro_lote_engenharia_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')
                ->constrained('financeiro_lote_engenharias')
                ->cascadeOnDelete();

            $table->date('data_lancamento');
            $table->date('data_pagamento')->nullable();

            $table->string('cliente_nome'); // embarcação/cliente
            $table->string('servico_tipo');

            $table->decimal('receita', 12, 2)->unsigned();
            $table->decimal('custos_extras', 12, 2)->unsigned()->default(0);
            $table->decimal('custo_total', 12, 2)->unsigned();
            $table->decimal('lucro', 12, 2);

            $table->boolean('nota_emitida')->default(false);

            $table->timestamps();

            $table->index(['lote_id', 'data_lancamento']);
            $table->index(['lote_id', 'nota_emitida']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financeiro_lote_engenharia_items');
    }
};
