<?php

use App\Support\DocumentoModeloCatalogoPadrao;
use App\Support\DocumentoModeloSincroniaDiscoBd;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_modelo_globais', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('titulo', 160);
            $table->string('referencia', 160)->nullable();
            $table->longText('conteudo');
            $table->timestamps();
        });

        Schema::table('documento_modelos', function (Blueprint $table) {
            $table->foreignId('documento_modelo_global_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('documento_modelo_globais')
                ->nullOnDelete();
            $table->boolean('personalizado')->default(false)->after('documento_modelo_global_id');
            $table->timestamp('global_synced_at')->nullable()->after('personalizado');
        });

        $now = now();
        foreach (DocumentoModeloCatalogoPadrao::mapaFicheirosRelativos() as $slug => $meta) {
            $conteudo = DocumentoModeloCatalogoPadrao::conteudoDoFicheiroPadrao($slug);
            if ($conteudo === null) {
                continue;
            }
            DB::table('documento_modelo_globais')->insert([
                'slug' => $slug,
                'titulo' => $meta['titulo'],
                'referencia' => $meta['referencia'] ?? null,
                'conteudo' => $conteudo,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $globais = DB::table('documento_modelo_globais')->get(['id', 'slug', 'conteudo']);
        foreach ($globais as $g) {
            $gc = DocumentoModeloSincroniaDiscoBd::normalizarQuebrasLinha((string) $g->conteudo);
            $rows = DB::table('documento_modelos')->where('slug', $g->slug)->get(['id', 'conteudo']);
            foreach ($rows as $row) {
                $ec = DocumentoModeloSincroniaDiscoBd::normalizarQuebrasLinha((string) $row->conteudo);
                $personalizado = $ec !== $gc;
                DB::table('documento_modelos')->where('id', $row->id)->update([
                    'documento_modelo_global_id' => $g->id,
                    'personalizado' => $personalizado,
                    'global_synced_at' => $personalizado ? null : $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('documento_modelos', function (Blueprint $table) {
            $table->dropForeign(['documento_modelo_global_id']);
            $table->dropColumn(['documento_modelo_global_id', 'personalizado', 'global_synced_at']);
        });

        Schema::dropIfExists('documento_modelo_globais');
    }
};
