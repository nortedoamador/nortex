<?php

use App\Enums\TipoProcessoCategoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @return list<string> */
    private static function servicoSlugs(): array
    {
        return array_map(
            static fn (TipoProcessoCategoria $c) => $c->value,
            TipoProcessoCategoria::cases(),
        );
    }

    /** @return list<string> */
    private static function anexoSlugs(): array
    {
        return [
            'cnh',
            'comprovante-endereco',
            'tie',
            'seguro-dpem',
            'foto-traves',
            'foto-popa',
            'foto-outras',
            'cha-digital',
            'cha-modelo-antigo',
        ];
    }

    public function up(): void
    {
        if (Schema::hasTable('platform_tipo_servicos')) {
            $this->seedTipoServicos();
        }

        if (Schema::hasTable('platform_anexo_tipos')) {
            $this->seedAnexoTipos();
            $this->backfillPlatformAnexoTipoId();
        }
    }

    private function seedTipoServicos(): void
    {
        $ordem = 0;
        foreach (TipoProcessoCategoria::cases() as $cat) {
            $slug = $cat->value;

            DB::table('platform_tipo_servicos')->updateOrInsert(
                ['slug' => $slug],
                [
                    'nome' => $cat->label(),
                    'ativo' => true,
                    'ordem' => $ordem++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function seedAnexoTipos(): void
    {
        /** @var list<array{slug: string, nome: string, ordem: int, is_multiple: bool}> $rows */
        $rows = [
            ['slug' => 'cnh', 'nome' => 'CNH', 'ordem' => 0, 'is_multiple' => false],
            ['slug' => 'comprovante-endereco', 'nome' => 'Comprovante de endereço', 'ordem' => 1, 'is_multiple' => true],
            ['slug' => 'tie', 'nome' => 'TIE', 'ordem' => 10, 'is_multiple' => true],
            ['slug' => 'seguro-dpem', 'nome' => 'Seguro DPEM', 'ordem' => 11, 'is_multiple' => true],
            ['slug' => 'foto-traves', 'nome' => 'Través (vista lateral)', 'ordem' => 12, 'is_multiple' => true],
            ['slug' => 'foto-popa', 'nome' => 'Foto da popa', 'ordem' => 13, 'is_multiple' => true],
            ['slug' => 'foto-outras', 'nome' => 'Outras fotos da embarcação', 'ordem' => 14, 'is_multiple' => true],
            ['slug' => 'cha-digital', 'nome' => 'CHA (digital)', 'ordem' => 20, 'is_multiple' => false],
            ['slug' => 'cha-modelo-antigo', 'nome' => 'CHA (modelo antigo)', 'ordem' => 21, 'is_multiple' => false],
        ];

        foreach ($rows as $r) {
            DB::table('platform_anexo_tipos')->updateOrInsert(
                ['slug' => $r['slug']],
                [
                    'nome' => $r['nome'],
                    'ativo' => true,
                    'ordem' => $r['ordem'],
                    'max_size_mb' => 20,
                    'allowed_mime_types' => null,
                    'allowed_extensions' => null,
                    'is_multiple' => $r['is_multiple'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function backfillPlatformAnexoTipoId(): void
    {
        /** @var array<string, string> tipo_codigo armazenado => slug da plataforma */
        $map = [
            'CNH' => 'cnh',
            'COMPROVANTE_ENDERECO' => 'comprovante-endereco',
            'TIE' => 'tie',
            'SEGURO_DPEM' => 'seguro-dpem',
            'FOTO_TRAVES' => 'foto-traves',
            'FOTO_POPA' => 'foto-popa',
            'FOTO_OUTRAS' => 'foto-outras',
            'CHA_DIGITAL' => 'cha-digital',
            'CHA_MODELO_ANTIGO' => 'cha-modelo-antigo',
        ];

        $slugToId = DB::table('platform_anexo_tipos')
            ->whereIn('slug', array_values($map))
            ->pluck('id', 'slug')
            ->all();

        foreach (['cliente_anexos', 'embarcacao_anexos', 'habilitacao_anexos'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'platform_anexo_tipo_id')) {
                continue;
            }

            foreach ($map as $codigo => $slug) {
                $tipoId = $slugToId[$slug] ?? null;
                if (! $tipoId) {
                    continue;
                }

                DB::table($table)
                    ->whereNull('platform_anexo_tipo_id')
                    ->where('tipo_codigo', $codigo)
                    ->update(['platform_anexo_tipo_id' => $tipoId]);
            }
        }
    }

    public function down(): void
    {
        $anexoSlugs = self::anexoSlugs();

        if (Schema::hasTable('platform_anexo_tipos') && $anexoSlugs !== []) {
            $ids = DB::table('platform_anexo_tipos')
                ->whereIn('slug', $anexoSlugs)
                ->pluck('id');

            foreach (['cliente_anexos', 'embarcacao_anexos', 'habilitacao_anexos'] as $table) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'platform_anexo_tipo_id')) {
                    continue;
                }
                DB::table($table)->whereIn('platform_anexo_tipo_id', $ids)->update(['platform_anexo_tipo_id' => null]);
            }

            DB::table('platform_anexo_tipos')->whereIn('slug', $anexoSlugs)->delete();
        }

        $servicoSlugs = self::servicoSlugs();

        if (Schema::hasTable('platform_tipo_servicos') && $servicoSlugs !== []) {
            DB::table('platform_tipo_servicos')->whereIn('slug', $servicoSlugs)->delete();
        }
    }
};
