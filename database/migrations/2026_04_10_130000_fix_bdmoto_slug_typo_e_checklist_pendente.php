<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Slug legado sem prefixo «anexo-» quebrava o render; alinha checklist pendente após corrigir o tipo.
 */
return new class extends Migration
{
    private const CODIGOS = ['TIE_BDMOTO_212_2B', 'TIE_BDMOTO_SE_ALTERACAO'];

    private const SLUG = 'anexo-2b-bdmoto-normam212';

    public function up(): void
    {
        DB::table('documento_tipos')
            ->where('modelo_slug', '2b-bdmoto-normam212')
            ->update([
                'modelo_slug' => self::SLUG,
                'updated_at' => now(),
            ]);

        DB::table('documento_tipos')
            ->whereIn('codigo', self::CODIGOS)
            ->where(function ($q) {
                $q->whereNull('modelo_slug')->orWhere('modelo_slug', '');
            })
            ->update([
                'modelo_slug' => self::SLUG,
                'auto_gerado' => false,
                'updated_at' => now(),
            ]);

        $tipoIds = DB::table('documento_tipos')
            ->whereIn('codigo', self::CODIGOS)
            ->pluck('id');

        if ($tipoIds->isEmpty()) {
            return;
        }

        DB::table('processo_documentos as pd')
            ->whereIn('pd.documento_tipo_id', $tipoIds)
            ->where('pd.status', 'pendente')
            ->where(function ($q) {
                $q->whereNull('pd.preenchido_via_modelo')->orWhere('pd.preenchido_via_modelo', false);
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw('1'))
                    ->from('processo_documento_anexos as pda')
                    ->whereColumn('pda.processo_documento_id', 'pd.id');
            })
            ->update([
                'pd.status' => 'enviado',
                'pd.preenchido_via_modelo' => true,
                'pd.updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // irreversível
    }
};
