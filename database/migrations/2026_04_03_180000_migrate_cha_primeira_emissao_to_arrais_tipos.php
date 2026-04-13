<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SLUG_ANTIGO = 'cha-primeira-habilitacao';

    private const SLUG_ARRAIS = 'cha-inscricao-arrais-amador';

    private const SLUG_MESTRE = 'cha-inscricao-arrais-amador-mestre-amador';

    private const SLUGS_PROVA = [self::SLUG_ARRAIS, self::SLUG_MESTRE];

    public function up(): void
    {
        DB::table('tipo_processos')
            ->where('slug', self::SLUG_ANTIGO)
            ->update([
                'slug' => self::SLUG_ARRAIS,
                'nome' => 'Inscrição e emissão de Arrais-Amador',
                'updated_at' => now(),
            ]);

        $idsInvalidos = DB::table('processos as p')
            ->join('tipo_processos as t', 'p.tipo_processo_id', '=', 't.id')
            ->where('p.status', 'aguardando_prova')
            ->whereNotIn('t.slug', self::SLUGS_PROVA)
            ->pluck('p.id');

        if ($idsInvalidos->isNotEmpty()) {
            DB::table('processos')
                ->whereIn('id', $idsInvalidos->all())
                ->update([
                    'status' => 'em_andamento',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('tipo_processos')
            ->where('slug', self::SLUG_ARRAIS)
            ->update([
                'slug' => self::SLUG_ANTIGO,
                'nome' => '1º Emissão da CHA',
                'updated_at' => now(),
            ]);
    }
};
