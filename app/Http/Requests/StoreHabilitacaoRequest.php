<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use App\Models\Habilitacao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHabilitacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', (string) $this->input('cpf')),
            ]);
        }

        foreach (['data_nascimento', 'data_emissao', 'data_validade'] as $key) {
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
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cliente_id' => [
                'required',
                Rule::exists('clientes', 'id')->where(
                    fn ($q) => $q->where('empresa_id', $this->user()->empresa_id),
                ),
            ],
            'cpf' => ['required', 'string', 'size:11', 'regex:/^[0-9]{11}$/'],
            'data_nascimento' => ['required', 'date', 'before_or_equal:today'],
            'categoria' => ['required', 'string', Rule::in(Habilitacao::CATEGORIAS_CHA)],
            'numero_cha' => ['required', 'string', 'max:128'],
            'data_emissao' => ['required', 'date'],
            'data_validade' => ['required', 'date', 'after_or_equal:data_emissao'],
            'jurisdicao' => ['required', 'string', Rule::in(Habilitacao::JURISDICOES)],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $clienteId = $this->input('cliente_id');
            $cpf = $this->input('cpf');
            if (! $clienteId || ! $cpf) {
                return;
            }
            $cliente = Cliente::query()->find($clienteId);
            if (! $cliente) {
                return;
            }
            $docCliente = preg_replace('/\D/', '', (string) ($cliente->cpf ?? ''));
            if (strlen($docCliente) !== 11) {
                $v->errors()->add('cliente_id', __('O cliente deve ser pessoa física (CPF) para vincular à CHA.'));

                return;
            }
            if ($docCliente !== $cpf) {
                $v->errors()->add('cpf', __('O CPF deve coincidir com o documento do cliente selecionado.'));
            }
        });
    }
}
