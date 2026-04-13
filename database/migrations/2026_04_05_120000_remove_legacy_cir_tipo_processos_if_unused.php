<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SLUGS_LEGACY = [
        'cir-inscricao-caderneta',
        'cir-atualizacao-cadastral',
    ];

    public function up(): void
    {
        $ids = DB::table('tipo_processos')
            ->whereIn('slug', self::SLUGS_LEGACY)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        $emUso = DB::table('processos')
            ->whereIn('tipo_processo_id', $ids->all())
            ->exists();

        if ($emUso) {
            return;
        }

        DB::table('documento_processo')
            ->whereIn('tipo_processo_id', $ids->all())
            ->delete();

        DB::table('tipo_processos')
            ->whereIn('id', $ids->all())
            ->delete();
    }

    public function down(): void
    {
        // Tipos legados não são recriados automaticamente; rode `EmpresaProcessosDefaultsService` em ambientes que precisem dos novos slugs CIR.
    }
};
