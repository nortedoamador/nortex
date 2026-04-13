<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embarcacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('nome');
            $table->string('registro')->nullable();
            $table->timestamps();
        });

        Schema::create('cliente_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('tipo_codigo', 64)->nullable()->index();
            $table->string('disk', 32)->default('public');
            $table->string('path');
            $table->string('nome_original');
            $table->string('mime', 128)->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->string('extra_validation_status', 32)->default('pendente');
            $table->text('extra_validation_notes')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('embarcacao_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('embarcacao_id')->constrained('embarcacoes')->cascadeOnDelete();
            $table->string('tipo_codigo', 64)->nullable()->index();
            $table->string('disk', 32)->default('public');
            $table->string('path');
            $table->string('nome_original');
            $table->string('mime', 128)->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->string('extra_validation_status', 32)->default('pendente');
            $table->text('extra_validation_notes')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embarcacao_anexos');
        Schema::dropIfExists('cliente_anexos');
        Schema::dropIfExists('embarcacoes');
    }
};
