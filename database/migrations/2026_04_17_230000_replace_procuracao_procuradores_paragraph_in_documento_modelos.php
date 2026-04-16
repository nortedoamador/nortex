<?php

use App\Support\DocumentoModeloTemplateAliases;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Substitui o parágrafo estático dos procuradores pelo texto vindo dos dados da empresa ($texto_procuracao_procuradores).
 */
return new class extends Migration
{
    public function up(): void
    {
        $body = DocumentoModeloTemplateAliases::TEXTO_PADRAO_PROCURACAO_PROCURADORES;
        $oldExact = "<p class=\"texto\">\n".$body."\n</p>";
        $new = <<<'BLADE'
<p class="texto">
{!! nl2br(e($texto_procuracao_procuradores)) !!}
</p>
BLADE;

        foreach (['documento_modelo_globais', 'documento_modelos'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            DB::table($table)->orderBy('id')->chunkById(50, function ($rows) use ($table, $oldExact, $new, $body): void {
                foreach ($rows as $row) {
                    $c = (string) ($row->conteudo ?? '');
                    if ($c === '' || str_contains($c, 'texto_procuracao_procuradores')) {
                        continue;
                    }
                    if (! str_contains($c, 'Nomeia e constitui os seus bastante procuradores')) {
                        continue;
                    }
                    $next = str_replace($oldExact, $new, $c, $count);
                    if ($count === 0) {
                        $pattern = '/<p\s+class="texto">\s*'.preg_quote($body, '/').'\s*<\/p>/s';
                        $preg = preg_replace($pattern, trim($new), $c);
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
        // Irreversível de forma segura (texto passa a vir da empresa).
    }
};
