<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escola_nauticas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->unique()->constrained('empresas')->cascadeOnDelete();
            $table->string('nome', 255);
            $table->string('cnpj', 20)->nullable();
            $table->foreignId('diretor_cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('escola_capitanias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_nautica_id')->constrained('escola_nauticas')->cascadeOnDelete();
            $table->string('funcao', 120)->nullable();
            $table->string('posto', 255)->nullable();
            $table->string('nome_capitao_portos', 255)->nullable();
            $table->text('endereco')->nullable();
            $table->timestamps();
        });

        Schema::create('escola_instrutores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('cha_numero', 64)->nullable();
            $table->string('cha_categoria', 80)->nullable();
            $table->date('cha_data_emissao')->nullable();
            $table->date('cha_data_validade')->nullable();
            $table->string('cha_jurisdicao', 255)->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'cliente_id']);
        });

        Schema::create('aula_nautica_escola_instrutores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_nautica_id')->constrained('aulas_nauticas')->cascadeOnDelete();
            $table->foreignId('escola_instrutor_id')->constrained('escola_instrutores')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['aula_nautica_id', 'escola_instrutor_id'], 'aula_escola_instr_unique');
        });

        Schema::create('aula_atestado_conteudo_duracoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_nautica_id')->constrained('aulas_nauticas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('programa', 8);
            $table->string('item_key', 48);
            $table->unsignedSmallInteger('duracao_minutos')->nullable();
            $table->timestamps();

            $table->unique(['aula_nautica_id', 'cliente_id', 'programa', 'item_key'], 'aula_atest_item_unique');
        });

        Schema::table('aulas_nauticas', function (Blueprint $table) {
            $table->timestamp('comunicado_enviado_em')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('aulas_nauticas', function (Blueprint $table) {
            $table->dropColumn('comunicado_enviado_em');
        });

        Schema::dropIfExists('aula_atestado_conteudo_duracoes');
        Schema::dropIfExists('aula_nautica_escola_instrutores');
        Schema::dropIfExists('escola_instrutores');
        Schema::dropIfExists('escola_capitanias');
        Schema::dropIfExists('escola_nauticas');
    }
};
