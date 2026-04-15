<?php

namespace App\Http\Requests;

use App\Enums\EmbarcacaoAreaNavegacao;
use App\Enums\EmbarcacaoTipoNavegacao;
use App\Enums\EmbarcacaoTipoPropulsao;
use App\Http\Requests\Concerns\ValidatesEmbarcacaoFotosOutrasRotulo;
use App\Models\Habilitacao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEmbarcacaoRequest extends FormRequest
{
    use ValidatesEmbarcacaoFotosOutrasRotulo;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('material_casco') === 'Outros') {
            $outro = trim((string) $this->input('material_casco_outro', ''));
            if ($outro !== '') {
                $this->merge(['material_casco' => $outro]);
            }
        }

        $tpRaw = trim((string) $this->input('tipo_propulsao', ''));
        $tipoPropulsao = $tpRaw === '' ? null : EmbarcacaoTipoPropulsao::tryFrom($tpRaw);
        $this->merge(['tipo_propulsao' => $tipoPropulsao?->value]);

        if ($tipoPropulsao !== null && ! $tipoPropulsao->incluiMotor()) {
            $this->merge([
                'motores' => [],
                'combustivel' => null,
            ]);
        } else {
            $rawMotores = $this->input('motores');
            if (! is_array($rawMotores)) {
                $this->merge(['motores' => []]);
            } else {
                $mergedMotores = [];
                foreach (array_slice($rawMotores, 0, 3) as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $marca = $item['marca'] ?? '';
                    $marca = is_string($marca) ? $marca : '';
                    if ($marca === 'Outros') {
                        $outro = trim((string) ($item['marca_outro'] ?? ''));
                        $marca = $outro !== '' ? $outro : '';
                    }
                    $mergedMotores[] = [
                        'marca' => $marca,
                        'potencia' => trim((string) ($item['potencia'] ?? '')),
                        'numero_serie' => trim((string) ($item['numero_serie'] ?? '')),
                    ];
                }
                $this->merge(['motores' => $mergedMotores]);
            }
        }

        $inscTrim = trim((string) $this->input('inscricao', ''));
        if ($inscTrim === '') {
            $this->merge([
                'inscricao' => null,
                'inscricao_data_emissao' => null,
                'inscricao_data_vencimento' => null,
                'inscricao_jurisdicao' => null,
                'alienacao_fiduciaria' => null,
                'credor_hipotecario' => null,
            ]);
        } else {
            $this->merge(['inscricao' => $inscTrim]);
            $alien = trim((string) $this->input('alienacao_fiduciaria', ''));
            if ($alien !== 'sim') {
                $this->merge(['credor_hipotecario' => null]);
            }
        }

        foreach (['inscricao_data_emissao', 'inscricao_data_vencimento'] as $key) {
            $v = $this->input($key);
            if (! is_string($v)) {
                continue;
            }
            $v = trim($v);
            if ($v === '') {
                continue;
            }
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                try {
                    $this->merge([$key => Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d')]);
                } catch (\Throwable) {
                    // ignora; validação acusará erro
                }
            }
        }

        $tipoRaw = trim((string) $this->input('tipo_navegacao', ''));
        $tipoVal = $tipoRaw === '' ? null : $tipoRaw;

        $areaRaw = trim((string) $this->input('area_navegacao', ''));
        $areaVal = $areaRaw === '' ? null : $areaRaw;

        if ($tipoVal === null && $areaVal !== null) {
            if ($areaVal === EmbarcacaoAreaNavegacao::Interior->value) {
                $tipoVal = EmbarcacaoTipoNavegacao::Interior->value;
            } elseif (in_array($areaVal, [
                EmbarcacaoAreaNavegacao::Costeira->value,
                EmbarcacaoAreaNavegacao::Oceanica->value,
            ], true)) {
                $tipoVal = EmbarcacaoTipoNavegacao::MarAberto->value;
            }
        }

        $this->merge(['tipo_navegacao' => $tipoVal]);

        if ($tipoVal === EmbarcacaoTipoNavegacao::Interior->value) {
            $this->merge(['area_navegacao' => EmbarcacaoAreaNavegacao::Interior->value]);
        } else {
            $this->merge(['area_navegacao' => $areaVal]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $tipo = $this->input('tipo_navegacao');
            if ($tipo !== EmbarcacaoTipoNavegacao::MarAberto->value) {
                return;
            }
            $area = $this->input('area_navegacao');
            if (! in_array($area, [
                EmbarcacaoAreaNavegacao::Costeira->value,
                EmbarcacaoAreaNavegacao::Oceanica->value,
            ], true)) {
                $v->errors()->add(
                    'area_navegacao',
                    __('Para mar aberto, selecione Costeira ou Oceânica.'),
                );
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cliente_id' => [
                'nullable',
                Rule::exists('clientes', 'id')->where(
                    fn ($q) => $q->where('empresa_id', $this->user()->empresa_id),
                ),
            ],
            'registro' => ['nullable', 'string', 'max:128'],
            'cpf' => ['nullable', 'string', 'max:20'],
            'inscricao' => ['nullable', 'string', 'max:128'],
            'inscricao_data_emissao' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => filled($this->input('inscricao'))),
            ],
            'inscricao_data_vencimento' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => filled($this->input('inscricao'))),
            ],
            'inscricao_jurisdicao' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => filled($this->input('inscricao'))),
                Rule::in(Habilitacao::JURISDICOES),
            ],
            'alienacao_fiduciaria' => [
                'nullable',
                'string',
                Rule::requiredIf(fn () => filled($this->input('inscricao'))),
                Rule::in(['sim', 'nao']),
            ],
            'credor_hipotecario' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => filled($this->input('inscricao')) && $this->input('alienacao_fiduciaria') === 'sim'),
            ],
            'cpi' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'string', 'max:32'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'uf' => ['nullable', 'string', 'size:2'],
            'nome_casco' => ['nullable', 'string', 'max:120'],
            'cor_casco' => ['nullable', 'string', 'max:80'],
            'tipo' => ['nullable', 'string', 'max:80'],
            'atividade' => ['nullable', 'string', 'max:80'],
            'tipo_navegacao' => ['nullable', 'string', Rule::enum(EmbarcacaoTipoNavegacao::class)],
            'area_navegacao' => ['nullable', 'string', Rule::enum(EmbarcacaoAreaNavegacao::class)],
            'combustivel' => ['nullable', 'string', 'max:80'],
            'ano_fabricacao' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'comprimento_m' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'boca_m' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'pontal_m' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'tonelagem' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'passageiros' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'compartimentos' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'tipo_propulsao' => ['nullable', 'string', Rule::enum(EmbarcacaoTipoPropulsao::class)],
            'propulsao_motor' => ['nullable', 'string', 'max:120'],
            'propulsao_leme' => ['nullable', 'string', 'max:120'],
            'altura_proa_m' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'altura_popa_m' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'porto_cidade' => ['nullable', 'string', 'max:120'],
            'porto_uf' => ['nullable', 'string', 'size:2'],
            'refit_ano' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'refit_local' => ['nullable', 'string', 'max:120'],
            'responsavel_refit' => ['nullable', 'string', 'max:120'],
            'pontal' => ['nullable', 'string', 'max:64'],
            'calado' => ['nullable', 'string', 'max:64'],
            'contorno' => ['nullable', 'string', 'max:64'],
            'calado_leve' => ['nullable', 'string', 'max:64'],
            'calado_carregado' => ['nullable', 'string', 'max:64'],
            'material_casco' => ['nullable', 'string', 'max:120'],
            'material_casco_outro' => ['nullable', 'string', 'max:120'],
            'numero_casco' => ['nullable', 'string', 'max:120'],
            'potencia_maxima_casco' => ['nullable', 'string', 'max:120'],
            'cor_casco_ficha' => ['nullable', 'string', 'max:120'],
            'construtor' => ['nullable', 'string', 'max:120'],
            'ano_construcao' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'tripulantes' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'comprimento' => ['nullable', 'string', 'max:64'],
            'boca' => ['nullable', 'string', 'max:64'],
            'arqueacao_bruta' => ['nullable', 'string', 'max:64'],
            'arqueacao_liquida' => ['nullable', 'string', 'max:64'],
            'motores' => ['nullable', 'array', 'max:3'],
            'motores.*.marca' => ['nullable', 'string', 'max:120'],
            'motores.*.potencia' => ['nullable', 'string', 'max:120'],
            'motores.*.numero_serie' => ['nullable', 'string', 'max:120'],
            'foto_traves' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'foto_popa' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'fotos_outras' => ['nullable', 'array', 'max:30'],
            'fotos_outras.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'fotos_outras_rotulo' => ['nullable', 'string', 'max:255'],
            'nf_numero' => ['nullable', 'string', 'max:64'],
            'nf_data' => ['nullable', 'date'],
            'nf_vendedor' => ['nullable', 'string', 'max:120'],
            'nf_local' => ['nullable', 'string', 'max:120'],
            'nf_documento_vendedor' => ['nullable', 'string', 'max:40'],
        ];
    }
}
