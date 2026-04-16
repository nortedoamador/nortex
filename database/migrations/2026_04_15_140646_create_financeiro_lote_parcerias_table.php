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
        if (Schema::hasTable('financeiro_lote_parcerias')) {
            return;
        }

        Schema::create('financeiro_lote_parcerias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->string('mes_referencia'); // YYYY-MM
            $table->string('empresa_parceira');
            $table->string('status_pagamento')->default('Em aberto'); // Pago | Em aberto

            $table->string('comprovante_path')->nullable();

            $table->timestamps();

            $table->index(['empresa_id', 'mes_referencia']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financeiro_lote_parcerias');
    }
};
