<?php

namespace App\Http\Requests;

use App\Enums\TipoProcessoCategoria;
use App\Models\Cliente;
use App\Models\Embarcacao;
use App\Models\Habilitacao;
use App\Models\PlatformTipoProcesso;
use App\Models\Processo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProcessoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Processo::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', (string) $this->input('cpf')),
            ]);
        }

        // Retrocompat: telas/clients antigos enviavam tipo_processo_id (tenant).
        if ($this->has('tipo_processo_id') && ! $this->has('platform_tipo_processo_id')) {
            $this->merge([
                'platform_tipo_processo_id' => $this->input('tipo_processo_id'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $empresaId = (int) $this->user()->empresa_id;

        return [
            'platform_tipo_processo_id' => [
                'required',
                'integer',
                Rule::exists('platform_tipo_processos', 'id')->where('ativo', true),
            ],
            'cliente_id' => [
                'required',
                'integer',
                Rule::exists('clientes', 'id')->where('empresa_id', $empresaId),
            ],
            'embarcacao_id' => ['nullable', 'integer'],
            'habilitacao_id' => ['nullable', 'integer'],
            'cpf' => ['required', 'string', 'size:11', 'regex:/^[0-9]{11}$/'],
            'nome_interessado' => ['required', 'string', 'max:255'],
            'jurisdicao' => ['required', 'string', Rule::in(Habilitacao::JURISDICOES)],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            '_novo_processo_passo' => ['nullable', 'string', 'in:inicio,detalhes'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $clienteId = $this->input('cliente_id');
            $cpf = $this->input('cpf');
            $nome = $this->input('nome_interessado');
            if (! $clienteId || ! $cpf) {
                return;
            }
            $cliente = Cliente::query()->find($clienteId);
            if (! $cliente) {
                return;
            }
            $docCliente = preg_replace('/\D/', '', (string) ($cliente->cpf ?? ''));
            if (strlen($docCliente) !== 11) {
                $v->errors()->add('cliente_id', __('O cliente deve ter CPF cadastrado para vincular ao processo.'));

                return;
            }
            if ($docCliente !== $cpf) {
                $v->errors()->add('cpf', __('O CPF deve coincidir com o documento do cliente selecionado.'));
            }
            $nomeCliente = trim((string) $cliente->nome);
            $nomeInformado = trim((string) $nome);
            if ($nomeCliente !== $nomeInformado) {
                $v->errors()->add('nome_interessado', __('O nome completo deve coincidir com o cadastro do cliente.'));
            }

            $tipoId = (int) ($this->input('platform_tipo_processo_id') ?? 0);
            if ($tipoId > 0) {
                $tipo = PlatformTipoProcesso::query()->find($tipoId);
                if ($tipo?->categoria?->value === 'embarcacao') {
                    $embarcacaoId = (int) ($this->input('embarcacao_id') ?? 0);
                    if ($embarcacaoId <= 0) {
                        $v->errors()->add('embarcacao_id', __('Selecione a embarcação deste processo.'));
                    } else {
                        $emb = Embarcacao::query()
                            ->where('empresa_id', (int) $this->user()->empresa_id)
                            ->where('cliente_id', (int) $cliente->id)
                            ->whereKey($embarcacaoId)
                            ->first();
                        if (! $emb) {
                            $v->errors()->add('embarcacao_id', __('A embarcação selecionada não pertence ao cliente.'));
                        }
                    }
                }

                if ($tipo
                    && $tipo->categoria === TipoProcessoCategoria::Cha
                    && in_array((string) $tipo->slug, PlatformTipoProcesso::SLUGS_EXIGEM_HABILITACAO_CHA_SELECIONADA, true)) {
                    $habId = (int) ($this->input('habilitacao_id') ?? 0);
                    if ($habId <= 0) {
                        $v->errors()->add('habilitacao_id', __('Selecione a CHA (habilitação) do cliente para este serviço.'));
                    } else {
                        $hab = Habilitacao::query()
                            ->where('empresa_id', (int) $this->user()->empresa_id)
                            ->where('cliente_id', (int) $cliente->id)
                            ->whereKey($habId)
                            ->first();
                        if (! $hab) {
                            $v->errors()->add('habilitacao_id', __('A habilitação selecionada não pertence ao cliente.'));
                        }
                    }
                } elseif ((int) ($this->input('habilitacao_id') ?? 0) > 0) {
                    $habId = (int) $this->input('habilitacao_id');
                    $hab = Habilitacao::query()
                        ->where('empresa_id', (int) $this->user()->empresa_id)
                        ->where('cliente_id', (int) $cliente->id)
                        ->whereKey($habId)
                        ->first();
                    if (! $hab) {
                        $v->errors()->add('habilitacao_id', __('A habilitação selecionada não pertence ao cliente.'));
                    }
                }
            }
        });
    }
}
