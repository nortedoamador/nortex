<?php

use App\Models\ProcessoDocumento;
use Illuminate\Database\Migrations\Migration;

/**
 * Itens com modelo PDF (Anexo 2-A, 2-B, 2-C, etc.) passam a contar como satisfeitos via modelo ao criar processo;
 * esta migração alinha processos já existentes que ficaram «pendentes» sem preenchido_via_modelo.
 */
return new class extends Migration
{
    public function up(): void
    {
        ProcessoDocumento::withoutGlobalScopes()
            ->where('status', 'pendente')
            ->where('preenchido_via_modelo', false)
            ->whereHas('documentoTipo', function ($q) {
                $q->whereNotNull('modelo_slug')
                    ->where('modelo_slug', '!=', '')
                    ->whereNotIn('modelo_slug', ['anexo-2g', 'anexo-5h', 'anexo-5d']);
            })
            ->update([
                'status' => 'enviado',
                'preenchido_via_modelo' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // irreversível sem histórico do estado anterior
    }
};
