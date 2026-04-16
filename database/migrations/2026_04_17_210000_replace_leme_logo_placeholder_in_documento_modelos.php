<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Substitui o HTML legado img/leme-logo.png pelo logótipo da empresa ({{ $logo_empresa_url }}).
 */
return new class extends Migration
{
    private const OLD_SNIPPET = '<p><img style="display: block; margin-left: auto; margin-right: auto;" src="img/leme-logo.png" alt="" width="360" height="140" /></p>';

    private const NEW_SNIPPET = <<<'BLADE'
@if(!empty($logo_empresa_url))
<p><img style="display: block; margin-left: auto; margin-right: auto;" src="{{ $logo_empresa_url }}" alt="{{ $nome_empresa }}" width="360" height="140" /></p>
@endif
BLADE;

    public function up(): void
    {
        foreach (['documento_modelo_globais', 'documento_modelos'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            DB::table($table)->orderBy('id')->chunkById(50, function ($rows) use ($table): void {
                foreach ($rows as $row) {
                    $c = (string) ($row->conteudo ?? '');
                    if ($c === '' || ! str_contains($c, 'leme-logo')) {
                        continue;
                    }
                    $count = 0;
                    $next = str_replace(self::OLD_SNIPPET, self::NEW_SNIPPET, $c, $count);
                    if ($count === 0) {
                        $preg = preg_replace(
                            '/<p><img[^>]*src="img\/leme-logo\.png"[^>]*><\/p>/',
                            trim(self::NEW_SNIPPET),
                            $c
                        );
                        $next = is_string($preg) ? $preg : $c;
                    }
                    if ($next === $c) {
                        continue;
                    }
                    DB::table($table)->where('id', $row->id)->update([
                        'conteudo' => $next,
                        'updated_at' => now(),
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        // Irreversível de forma segura (não repor URL estática).
    }
};
