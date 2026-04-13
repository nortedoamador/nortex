<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_tipo_processos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug', 128)->unique();
            $table->string('categoria', 32)->nullable()->index();
            $table->boolean('ativo')->default(true)->index();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('platform_tipo_servicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug', 128)->unique();
            $table->boolean('ativo')->default(true)->index();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('platform_anexo_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug', 128)->unique();
            $table->boolean('ativo')->default(true)->index();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->unsignedSmallInteger('max_size_mb')->default(20);
            $table->json('allowed_mime_types')->nullable();
            $table->json('allowed_extensions')->nullable();
            $table->boolean('is_multiple')->default(true);
            $table->timestamps();
        });

        Schema::table('processos', function (Blueprint $table) {
            $table->foreignId('platform_tipo_processo_id')
                ->nullable()
                ->after('tipo_processo_id')
                ->constrained('platform_tipo_processos')
                ->nullOnDelete();
        });

        Schema::table('documento_processo', function (Blueprint $table) {
            $table->foreignId('empresa_id')
                ->nullable()
                ->after('id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->foreignId('platform_tipo_processo_id')
                ->nullable()
                ->after('tipo_processo_id')
                ->constrained('platform_tipo_processos')
                ->cascadeOnDelete();

            $table->unique(['empresa_id', 'platform_tipo_processo_id', 'documento_tipo_id'], 'doc_proc_emp_plat_doc_unique');
        });

        foreach (['cliente_anexos', 'embarcacao_anexos', 'habilitacao_anexos'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('platform_anexo_tipo_id')
                    ->nullable()
                    ->after('tipo_codigo')
                    ->constrained('platform_anexo_tipos')
                    ->nullOnDelete();
            });
        }

        $this->migrarTiposDeProcessoParaPlataforma();
        $this->backfillProcessosEChecklistParaPlataforma();
    }

    private function migrarTiposDeProcessoParaPlataforma(): void
    {
        if (! Schema::hasTable('tipo_processos')) {
            return;
        }

        $rows = DB::table('tipo_processos')
            ->selectRaw('MIN(nome) as nome, slug, MIN(categoria) as categoria')
            ->groupBy('slug')
            ->get();

        foreach ($rows as $r) {
            DB::table('platform_tipo_processos')->updateOrInsert(
                ['slug' => $r->slug],
                [
                    'nome' => $r->nome,
                    'categoria' => $r->categoria,
                    'ativo' => true,
                    'ordem' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function backfillProcessosEChecklistParaPlataforma(): void
    {
        if (! Schema::hasTable('tipo_processos')) {
            return;
        }

        $driver = (string) DB::getDriverName();

        // SQLite (tests) não suporta UPDATE ... JOIN. Fazemos backfill em PHP.
        if ($driver === 'sqlite') {
            $tipoToPlatform = [];
            $tipoRows = DB::table('tipo_processos')->select(['id', 'empresa_id', 'slug'])->get();
            foreach ($tipoRows as $tp) {
                $ptpId = DB::table('platform_tipo_processos')->where('slug', $tp->slug)->value('id');
                if ($ptpId) {
                    $tipoToPlatform[(int) $tp->id] = [
                        'platform_tipo_processo_id' => (int) $ptpId,
                        'empresa_id' => (int) $tp->empresa_id,
                    ];
                }
            }

            foreach ($tipoToPlatform as $tipoId => $data) {
                DB::table('processos')
                    ->whereNull('platform_tipo_processo_id')
                    ->where('tipo_processo_id', $tipoId)
                    ->update(['platform_tipo_processo_id' => $data['platform_tipo_processo_id']]);

                DB::table('documento_processo')
                    ->where('tipo_processo_id', $tipoId)
                    ->update([
                        'platform_tipo_processo_id' => $data['platform_tipo_processo_id'],
                        'empresa_id' => $data['empresa_id'],
                    ]);
            }

            return;
        }

        // processos.platform_tipo_processo_id a partir do slug do tipo (por empresa)
        DB::statement(
            "UPDATE processos p
             JOIN tipo_processos tp ON tp.id = p.tipo_processo_id
             JOIN platform_tipo_processos ptp ON ptp.slug = tp.slug
             SET p.platform_tipo_processo_id = ptp.id
             WHERE p.platform_tipo_processo_id IS NULL"
        );

        // documento_processo: empresa_id + platform_tipo_processo_id
        DB::statement(
            "UPDATE documento_processo dp
             JOIN tipo_processos tp ON tp.id = dp.tipo_processo_id
             JOIN platform_tipo_processos ptp ON ptp.slug = tp.slug
             SET dp.platform_tipo_processo_id = ptp.id,
                 dp.empresa_id = tp.empresa_id
             WHERE dp.platform_tipo_processo_id IS NULL
                OR dp.empresa_id IS NULL"
        );
    }

    public function down(): void
    {
        foreach (['cliente_anexos', 'embarcacao_anexos', 'habilitacao_anexos'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('platform_anexo_tipo_id');
            });
        }

        Schema::table('documento_processo', function (Blueprint $table) {
            $table->dropUnique('doc_proc_emp_plat_doc_unique');
            $table->dropConstrainedForeignId('platform_tipo_processo_id');
            $table->dropConstrainedForeignId('empresa_id');
        });

        Schema::table('processos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('platform_tipo_processo_id');
        });

        Schema::dropIfExists('platform_anexo_tipos');
        Schema::dropIfExists('platform_tipo_servicos');
        Schema::dropIfExists('platform_tipo_processos');
    }
};

