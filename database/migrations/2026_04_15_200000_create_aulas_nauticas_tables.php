<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas_nauticas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('numero_oficio', 50);
            $table->date('data_aula');
            $table->string('local', 255);
            $table->string('categoria', 80);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fim')->nullable();

            $table->string('status', 30)->default('rascunho');
            $table->timestamps();

            $table->unique(['empresa_id', 'numero_oficio']);
            $table->index(['empresa_id', 'data_aula']);
        });

        Schema::create('aula_nautica_alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_nautica_id')->constrained('aulas_nauticas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['aula_nautica_id', 'cliente_id']);
            $table->index(['cliente_id']);
        });

        Schema::create('aula_nautica_instrutores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_nautica_id')->constrained('aulas_nauticas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['aula_nautica_id', 'user_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aula_nautica_instrutores');
        Schema::dropIfExists('aula_nautica_alunos');
        Schema::dropIfExists('aulas_nauticas');
    }
};

