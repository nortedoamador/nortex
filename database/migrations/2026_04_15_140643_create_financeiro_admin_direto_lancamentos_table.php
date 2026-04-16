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
        if (Schema::hasTable('financeiro_admin_direto_lancamentos')) {
            return;
        }

        Schema::create('financeiro_admin_direto_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->date('data_servico');
            $table->date('data_pagamento')->nullable();

            $table->string('cliente_nome');
            $table->string('servico_tipo');
            $table->string('status_pagamento')->default('Em aberto'); // Pago | Em aberto

            $table->decimal('receita', 12, 2)->unsigned();
            $table->decimal('taxa_marinha', 12, 2)->unsigned()->default(0);
            $table->decimal('custo_envio', 12, 2)->unsigned()->default(0);
            $table->decimal('custo_total', 12, 2)->unsigned();
            $table->decimal('lucro', 12, 2);

            $table->string('comprovante_path')->nullable();

            $table->timestamps();

            // MySQL index identifiers are limited (e.g. 64 chars). Explicit names avoid "identifier name too long".
            $table->index(['empresa_id', 'data_servico'], 'fin_ad_lanc_emp_data_idx');
            $table->index(['empresa_id', 'status_pagamento'], 'fin_ad_lanc_emp_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financeiro_admin_direto_lancamentos');
    }
};
