<?php

namespace App\Support;

use App\Models\Embarcacao;
use Illuminate\Support\Collection;

final class EmbarcacaoFotosGaleria
{
    /**
     * @return Collection<int, array{anexo: \App\Models\EmbarcacaoAnexo, label: string, tipo: string}>
     */
    public static function itensOrdenados(Embarcacao $embarcacao): Collection
    {
        $porTipo = [
            EmbarcacaoTiposAnexo::FOTO_TRAVES => __('Vista lateral (través)'),
            EmbarcacaoTiposAnexo::FOTO_POPA => __('Vista traseira'),
        ];
        $itens = collect();
        foreach ([EmbarcacaoTiposAnexo::FOTO_TRAVES, EmbarcacaoTiposAnexo::FOTO_POPA] as $codigo) {
            foreach ($embarcacao->anexos->where('tipo_codigo', $codigo) as $anexo) {
                $itens->push([
                    'anexo' => $anexo,
                    'label' => $porTipo[$codigo],
                    'tipo' => $codigo,
                ]);
            }
        }
        $outras = $embarcacao->anexos->where('tipo_codigo', EmbarcacaoTiposAnexo::FOTO_OUTRAS)->values();
        $nOutras = $outras->count();
        foreach ($outras as $idx => $anexo) {
            $rotulo = filled($anexo->rotulo ?? null) ? (string) $anexo->rotulo : __('Outras fotos');
            $label = $nOutras > 1 ? $rotulo.' ('.($idx + 1).')' : $rotulo;
            $itens->push([
                'anexo' => $anexo,
                'label' => $label,
                'tipo' => EmbarcacaoTiposAnexo::FOTO_OUTRAS,
            ]);
        }

        return $itens;
    }
}
