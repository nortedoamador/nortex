<?php

use App\Models\DocumentoTipo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('documento_tipos')) {
            return;
        }

        $oldCodigo = 'BSADE_NORMAM_2D';
        $newCodigo = 'TIE_BSADE_211_2B_DUAS_VIAS';

        /** @var DocumentoTipo|null $old */
        $old = DocumentoTipo::withoutGlobalScopes()->where('codigo', $oldCodigo)->orderBy('id')->first();
        if (! $old) {
            return;
        }

        $empresaId = (int) $old->empresa_id;

        /** @var DocumentoTipo|null $new */
        $new = DocumentoTipo::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('codigo', $newCodigo)
            ->first();

        if (! $new) {
            $new = DocumentoTipo::withoutGlobalScopes()->create([
                'empresa_id' => $empresaId,
                'codigo' => $newCodigo,
                'nome' => 'Boletim Simplificado de Atualização de Dados de Embarcação — BSADE (Anexo 2-B da NORMAM-211), em duas vias.',
            ]);
        }

        DB::table('documento_processo')
            ->where('empresa_id', $empresaId)
            ->where('documento_tipo_id', (int) $old->id)
            ->update(['documento_tipo_id' => (int) $new->id]);

        DB::table('processo_documentos')
            ->whereIn('processo_id', function ($q) use ($empresaId) {
                $q->select('id')->from('processos')->where('empresa_id', $empresaId);
            })
            ->where('documento_tipo_id', (int) $old->id)
            ->update(['documento_tipo_id' => (int) $new->id]);
    }

    public function down(): void
    {
        // Sem rollback automático: manteremos os IDs re-pointados para evitar regressão.
    }
};

