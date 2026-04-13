<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legados = DB::table('tipo_processos')->where('slug', 'habilitacao')->get();

        foreach ($legados as $old) {
            $novoId = DB::table('tipo_processos')
                ->where('empresa_id', $old->empresa_id)
                ->where('slug', 'cha-primeira-habilitacao')
                ->value('id');

            if (! $novoId) {
                $novoId = DB::table('tipo_processos')
                    ->where('empresa_id', $old->empresa_id)
                    ->where('categoria', 'cha')
                    ->where('id', '!=', $old->id)
                    ->orderBy('id')
                    ->value('id');
            }

            $procCount = (int) DB::table('processos')->where('tipo_processo_id', $old->id)->count();

            if ($novoId !== null) {
                DB::table('processos')->where('tipo_processo_id', $old->id)->update(['tipo_processo_id' => $novoId]);
            }

            if ($procCount === 0 || $novoId !== null) {
                DB::table('documento_processo')->where('tipo_processo_id', $old->id)->delete();
                DB::table('tipo_processos')->where('id', $old->id)->delete();
            }
        }
    }

    public function down(): void
    {
        // irreversível: tipo legado não é recriado automaticamente
    }
};
