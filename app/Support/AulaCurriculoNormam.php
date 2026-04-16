<?php

namespace App\Support;

/**
 * Itens de conteúdo para atestados ARA / MTA (planos teórico e prático).
 *
 * @phpstan-type Item array{key: string, label: string}
 */
final class AulaCurriculoNormam
{
    public const PROGRAMA_ARA = 'ara';

    public const PROGRAMA_MTA = 'mta';

    /**
     * @return array{teorico: list<Item>, pratico: list<Item>}
     */
    public static function itensAra(): array
    {
        return [
            'teorico' => [
                ['key' => 'ara_t_01', 'label' => 'Apresentação da embarcação'],
                ['key' => 'ara_t_02', 'label' => 'Apresentação das regras de governo'],
                ['key' => 'ara_t_03', 'label' => 'Luzes e marcas'],
                ['key' => 'ara_t_04', 'label' => 'Providências para saída/chegada e para manutenção preventiva da embarcação'],
                ['key' => 'ara_t_05', 'label' => 'Funcionamento e utilização do transceptor de VHF'],
                ['key' => 'ara_t_06', 'label' => 'Frequência/Chamada de socorro/Urgência'],
                ['key' => 'ara_t_07', 'label' => 'Exemplos práticos de primeiros socorros à bordo'],
                ['key' => 'ara_t_08', 'label' => 'Noções de combate à incêndio'],
                ['key' => 'ara_t_09', 'label' => 'Pontos de ignição e de Fulgor dos combustíveis (gasolina, etano e diesel)'],
                ['key' => 'ara_t_10', 'label' => 'Procedimentos para abastecimento (ventilação, uso do suspiro, etc)'],
                ['key' => 'ara_t_11', 'label' => 'Noções de sobrevivência e segurança'],
                ['key' => 'ara_t_12', 'label' => 'Tipos de materiais de segurança e salvatagem'],
            ],
            'pratico' => [
                ['key' => 'ara_p_01', 'label' => 'Preparar a embarcação para navegar'],
                ['key' => 'ara_p_02', 'label' => 'Demonstração dos procedimentos para abastecimento (ventilação, uso do suspiro, etc)'],
                ['key' => 'ara_p_03', 'label' => 'Demonstração de luzes, marcas e sinais sonoros'],
                ['key' => 'ara_p_04', 'label' => 'Demonstração das regras de governo'],
                ['key' => 'ara_p_05', 'label' => 'Demonstração da ação do Leme / Hélice'],
                ['key' => 'ara_p_06', 'label' => 'Execução de manobras de atração/desatracação/fundeio/suspender'],
                ['key' => 'ara_p_07', 'label' => 'Apresentação da saída e aproximação segura da margem'],
                ['key' => 'ara_p_08', 'label' => 'Execução da lista de verificação'],
            ],
        ];
    }

    /**
     * @return array{teorico: list<Item>, pratico: list<Item>}
     */
    public static function itensMta(): array
    {
        return [
            'teorico' => [
                ['key' => 'mta_t_01', 'label' => 'Apresentação da moto aquática'],
                ['key' => 'mta_t_02', 'label' => 'Apresentação das regras de governo'],
                ['key' => 'mta_t_03', 'label' => 'Apresentação das regras para saída e aproximação'],
                ['key' => 'mta_t_04', 'label' => 'Apresentação de situações práticas de emergência'],
                ['key' => 'mta_t_05', 'label' => 'Procedimentos para o transporte de passageiros'],
                ['key' => 'mta_t_06', 'label' => 'Utilização de equipamentos de segurança'],
            ],
            'pratico' => [
                ['key' => 'mta_p_01', 'label' => 'Realização de manobras e técnicas de pilotagem'],
                ['key' => 'mta_p_02', 'label' => 'Limites operacionais do equipamento'],
                ['key' => 'mta_p_03', 'label' => 'Execução das regras de governo'],
                ['key' => 'mta_p_04', 'label' => 'Execução de saída e aproximação de praias e margens'],
                ['key' => 'mta_p_05', 'label' => 'Execução de situações práticas de emergência'],
                ['key' => 'mta_p_06', 'label' => 'Utilização de equipamentos de segurança'],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function allKeys(string $programa): array
    {
        $blocos = $programa === self::PROGRAMA_MTA ? self::itensMta() : self::itensAra();
        $keys = [];
        foreach ($blocos as $grupo) {
            foreach ($grupo as $item) {
                $keys[] = $item['key'];
            }
        }

        return $keys;
    }
}
