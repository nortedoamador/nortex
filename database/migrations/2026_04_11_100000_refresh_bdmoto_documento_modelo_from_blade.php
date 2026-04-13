<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repõe o HTML do modelo BDMOTO a partir do ficheiro padrão (evita ver template antigo em cache na BD).
 */
return new class extends Migration
{
    private const SLUG = 'anexo-2b-bdmoto-normam212';

    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('documento_modelos')) {
            return;
        }

        $path = resource_path('views/documento-modelos/defaults/'.self::SLUG.'.blade.php');
        if (! is_file($path)) {
            return;
        }

        $conteudo = file_get_contents($path);
        if (! is_string($conteudo) || trim($conteudo) === '') {
            return;
        }

        DB::table('documento_modelos')
            ->where('slug', self::SLUG)
            ->update([
                'titulo' => 'ANEXO 2-B - BDMOTO (NORMAM 212)',
                'conteudo' => $conteudo,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // irreversível
    }
};
