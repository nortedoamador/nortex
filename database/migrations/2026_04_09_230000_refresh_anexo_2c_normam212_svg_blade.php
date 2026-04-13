<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Atualiza o modelo ANEXO 2-C (212) com o template SVG + Blade (Dompdf).
 */
return new class extends Migration
{
    private const SLUG = 'anexo-2c-normam212';

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

        $titulo = 'ANEXO 2-C - Declaração de perda/roubo/extravio de TIE (NORMAM 212)';
        $now = now();

        foreach (DB::table('empresas')->pluck('id') as $empresaId) {
            $exists = DB::table('documento_modelos')
                ->where('empresa_id', $empresaId)
                ->where('slug', self::SLUG)
                ->exists();

            if ($exists) {
                DB::table('documento_modelos')
                    ->where('empresa_id', $empresaId)
                    ->where('slug', self::SLUG)
                    ->update([
                        'titulo' => $titulo,
                        'conteudo' => $conteudo,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('documento_modelos')->insert([
                    'empresa_id' => $empresaId,
                    'slug' => self::SLUG,
                    'titulo' => $titulo,
                    'conteudo' => $conteudo,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // irreversível
    }
};
