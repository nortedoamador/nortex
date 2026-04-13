<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('cpf', 14)->nullable()->index();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('tipo_processos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['empresa_id', 'slug']);
        });

        Schema::create('documento_tipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('codigo');
            $table->string('nome');
            $table->timestamps();

            $table->unique(['empresa_id', 'codigo']);
        });

        Schema::create('documento_processo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_processo_id')->constrained('tipo_processos')->cascadeOnDelete();
            $table->foreignId('documento_tipo_id')->constrained('documento_tipos')->cascadeOnDelete();
            $table->boolean('obrigatorio')->default(true);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();

            $table->unique(['tipo_processo_id', 'documento_tipo_id'], 'doc_proc_tipo_doc_unique');
        });

        Schema::create('processos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('tipo_processo_id')->constrained('tipo_processos')->restrictOnDelete();
            $table->string('titulo')->nullable();
            $table->string('status', 64)->default('em_montagem')->index();
            $table->timestamps();
        });

        Schema::create('processo_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos')->cascadeOnDelete();
            $table->foreignId('documento_tipo_id')->constrained('documento_tipos')->restrictOnDelete();
            $table->string('status', 32)->default('pendente')->index();
            $table->timestamps();

            $table->unique(['processo_id', 'documento_tipo_id'], 'proc_doc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processo_documentos');
        Schema::dropIfExists('processos');
        Schema::dropIfExists('documento_processo');
        Schema::dropIfExists('documento_tipos');
        Schema::dropIfExists('tipo_processos');
        Schema::dropIfExists('clientes');
    }
};
