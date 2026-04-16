<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_atestado_normam_duracoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('programa', 8);
            $table->string('item_key', 48);
            $table->unsignedSmallInteger('duracao_minutos')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'programa', 'item_key'], 'empresa_normam_duracao_unique');
        });

        if (! Schema::hasTable('aula_atestado_conteudo_duracoes')) {
            return;
        }

        $aggregated = DB::table('aula_atestado_conteudo_duracoes as d')
            ->join('aulas_nauticas as an', 'an.id', '=', 'd.aula_nautica_id')
            ->whereNotNull('d.duracao_minutos')
            ->groupBy('an.empresa_id', 'd.programa', 'd.item_key')
            ->selectRaw('an.empresa_id as empresa_id, d.programa as programa, d.item_key as item_key, MAX(d.duracao_minutos) as duracao_minutos')
            ->get();

        $now = now();
        $rows = $aggregated->map(fn ($r) => [
            'empresa_id' => $r->empresa_id,
            'programa' => $r->programa,
            'item_key' => $r->item_key,
            'duracao_minutos' => $r->duracao_minutos,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows !== []) {
            DB::table('empresa_atestado_normam_duracoes')->upsert(
                $rows,
                ['empresa_id', 'programa', 'item_key'],
                ['duracao_minutos', 'updated_at']
            );
        }

        Schema::dropIfExists('aula_atestado_conteudo_duracoes');
    }

    public function down(): void
    {
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

        Schema::dropIfExists('empresa_atestado_normam_duracoes');
    }
};
