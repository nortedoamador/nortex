<?php

namespace App\Models;

use App\Enums\EmbarcacaoAreaNavegacao;
use App\Enums\EmbarcacaoTipoNavegacao;
use App\Enums\EmbarcacaoTipoPropulsao;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Embarcacao extends TenantModel
{
    /** Tabela em português; o plural automático seria `embarcacaos`. */
    protected $table = 'embarcacoes';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'cpf',
        'nome',
        'registro',
        'inscricao',
        'inscricao_data_emissao',
        'inscricao_data_vencimento',
        'inscricao_jurisdicao',
        'alienacao_fiduciaria',
        'credor_hipotecario',
        'cpi',
        'status',
        'cidade',
        'uf',
        'nome_casco',
        'cor_casco',
        'tipo',
        'atividade',
        'tipo_navegacao',
        'area_navegacao',
        'combustivel',
        'ano_fabricacao',
        'comprimento_m',
        'boca_m',
        'pontal_m',
        'tonelagem',
        'passageiros',
        'compartimentos',
        'tipo_propulsao',
        'propulsao_motor',
        'propulsao_leme',
        'altura_proa_m',
        'altura_popa_m',
        'porto_cidade',
        'porto_uf',
        'refit_ano',
        'refit_local',
        'responsavel_refit',
        'pontal',
        'calado',
        'contorno',
        'calado_leve',
        'calado_carregado',
        'material_casco',
        'numero_casco',
        'potencia_maxima_casco',
        'cor_casco_ficha',
        'construtor',
        'ano_construcao',
        'tripulantes',
        'comprimento',
        'boca',
        'arqueacao_bruta',
        'arqueacao_liquida',
        'marca_motor',
        'potencia_maxima_motor',
        'numero_motor',
        'motores',
        'nf_numero',
        'nf_data',
        'nf_vendedor',
        'nf_local',
        'nf_documento_vendedor',
        'escritura_cartorio',
        'escritura_numero',
        'escritura_data',
    ];

    /** CPF ou CNPJ do titular (coluna `cpf`) formatado para exibição / autocomplete. */
    public function cpfFormatadoTitular(): ?string
    {
        if ($this->cpf === null || $this->cpf === '') {
            return null;
        }
        $d = preg_replace('/\D/', '', $this->cpf);
        if (strlen($d) === 11) {
            return substr($d, 0, 3).'.'.substr($d, 3, 3).'.'.substr($d, 6, 3).'-'.substr($d, 9, 2);
        }
        if (strlen($d) === 14) {
            return substr($d, 0, 2).'.'.substr($d, 2, 3).'.'.substr($d, 5, 3).'/'.substr($d, 8, 4).'-'.substr($d, 12, 2);
        }

        return $this->cpf;
    }

    protected function casts(): array
    {
        return [
            'inscricao_data_emissao' => 'date',
            'inscricao_data_vencimento' => 'date',
            'tipo_navegacao' => EmbarcacaoTipoNavegacao::class,
            'area_navegacao' => EmbarcacaoAreaNavegacao::class,
            'tipo_propulsao' => EmbarcacaoTipoPropulsao::class,
            'motores' => 'array',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(EmbarcacaoAnexo::class);
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }

    /**
     * @param  array<int, mixed>|null  $motores
     * @return list<array{marca: string, potencia: string, numero_serie: string}>|null
     */
    public static function normalizeMotoresPayload(?array $motores): ?array
    {
        if ($motores === null || $motores === []) {
            return null;
        }

        $out = [];
        foreach (array_slice($motores, 0, 3) as $m) {
            if (! is_array($m)) {
                continue;
            }
            $marca = trim((string) ($m['marca'] ?? ''));
            $pot = trim((string) ($m['potencia'] ?? ''));
            $num = trim((string) ($m['numero_serie'] ?? ''));
            if ($marca === '' && $pot === '' && $num === '') {
                continue;
            }
            $out[] = [
                'marca' => $marca,
                'potencia' => $pot,
                'numero_serie' => $num,
            ];
        }

        return $out === [] ? null : $out;
    }

    /**
     * @param  list<array{marca: string, potencia: string, numero_serie: string}>|null  $motores
     * @return array{marca_motor: ?string, potencia_maxima_motor: ?string, numero_motor: ?string}
     */
    public static function legacyAttributesFromMotores(?array $motores): array
    {
        if ($motores === null || $motores === []) {
            return [
                'marca_motor' => null,
                'potencia_maxima_motor' => null,
                'numero_motor' => null,
            ];
        }

        foreach ($motores as $m) {
            $marca = trim((string) ($m['marca'] ?? ''));
            $pot = trim((string) ($m['potencia'] ?? ''));
            $num = trim((string) ($m['numero_serie'] ?? ''));
            if ($marca !== '' || $pot !== '' || $num !== '') {
                return [
                    'marca_motor' => $marca !== '' ? $marca : null,
                    'potencia_maxima_motor' => $pot !== '' ? $pot : null,
                    'numero_motor' => $num !== '' ? $num : null,
                ];
            }
        }

        return [
            'marca_motor' => null,
            'potencia_maxima_motor' => null,
            'numero_motor' => null,
        ];
    }

    /**
     * @return list<array{marca: string, potencia: string, numero_serie: string}>
     */
    public function motoresParaExibicao(): array
    {
        $raw = $this->motores;
        if (is_array($raw) && $raw !== []) {
            $out = [];
            foreach (array_slice($raw, 0, 3) as $m) {
                if (! is_array($m)) {
                    continue;
                }
                $marca = trim((string) ($m['marca'] ?? ''));
                $pot = trim((string) ($m['potencia'] ?? ''));
                $num = trim((string) ($m['numero_serie'] ?? ''));
                if ($marca === '' && $pot === '' && $num === '') {
                    continue;
                }
                $out[] = [
                    'marca' => $marca,
                    'potencia' => $pot,
                    'numero_serie' => $num,
                ];
            }
            if ($out !== []) {
                return $out;
            }
        }

        $marca = trim((string) ($this->marca_motor ?? ''));
        $pot = trim((string) ($this->potencia_maxima_motor ?? ''));
        $num = trim((string) ($this->numero_motor ?? ''));
        if ($marca === '' && $pot === '' && $num === '') {
            return [];
        }

        return [[
            'marca' => $marca,
            'potencia' => $pot,
            'numero_serie' => $num,
        ]];
    }
}
