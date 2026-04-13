<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesClienteFichaAnexos;
use App\Models\Cliente;
use App\Support\BrasilEstados;
use App\Support\BrasilOrgaoEmissorDocumento;
use App\Support\DocumentoBrasil;
use App\Support\NacionalidadesComuns;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    use ValidatesClienteFichaAnexos;

    public function authorize(): bool
    {
        return $this->user()->can('create', Cliente::class);
    }

    protected function prepareForValidation(): void
    {
        $nome = $this->input('nome');
        if (is_string($nome)) {
            $this->merge(['nome' => $this->normalizarNomePtBr($nome)]);
        }

        $presetOutro = $this->input('anexo_outro_tipo_preset');
        $customOutro = $this->input('anexo_outro_tipo_custom');
        if (is_string($presetOutro) || is_string($customOutro)) {
            $tipo = null;
            if (is_string($presetOutro) && trim($presetOutro) !== '' && trim($presetOutro) !== '__outro') {
                $tipo = trim($presetOutro);
            } elseif (is_string($customOutro) && trim($customOutro) !== '') {
                $tipo = trim($customOutro);
            }
            $this->merge(['anexo_outro_tipo' => $tipo]);
        }

        foreach (['complemento', 'apartamento', 'celular', 'documento_identidade_numero', 'documento_identidade_tipo', 'orgao_emissor', 'nome_pai', 'nome_mae', 'numero_cnh', 'categoria_cnh'] as $key) {
            if ($this->has($key) && is_string($this->input($key)) && trim($this->input($key)) === '') {
                $this->merge([$key => null]);
            }
        }

        // Compatibilidade: se vier "rg" (legado), mapeia para "documento_identidade_numero".
        if ($this->filled('rg') && ! $this->filled('documento_identidade_numero')) {
            $this->merge(['documento_identidade_numero' => (string) $this->input('rg')]);
        }

        // PJ: não exige RG/CNH; zera campos para evitar validação/armazenamento indevido.
        if ($this->input('tipo_documento') === 'pj') {
            $this->merge([
                'documento_identidade_tipo' => null,
                'documento_identidade_numero' => null,
                'orgao_emissor' => null,
                'data_emissao_rg' => null,
            ]);
        }

        if ($this->input('tipo_documento') === 'pf' && ! $this->filled('documento_identidade_tipo')) {
            $this->merge(['documento_identidade_tipo' => BrasilOrgaoEmissorDocumento::TIPO_CNH]);
        }

        if ($this->filled(['cpf', 'tipo_documento'])) {
            $d = DocumentoBrasil::apenasDigitos((string) $this->input('cpf'));
            if ($this->input('tipo_documento') === 'pf' && strlen($d) === 11) {
                $this->merge(['cpf' => DocumentoBrasil::formatarCpf($d)]);
            }
            if ($this->input('tipo_documento') === 'pj' && strlen($d) === 14) {
                $this->merge(['cpf' => DocumentoBrasil::formatarCnpj($d)]);
            }
        }

        // CIN: número do documento é o CPF (já formatado).
        if ($this->input('tipo_documento') === 'pf' && $this->input('documento_identidade_tipo') === \App\Support\BrasilOrgaoEmissorDocumento::TIPO_CIN) {
            $cpf = trim((string) $this->input('cpf', ''));
            if ($cpf !== '') {
                $this->merge(['documento_identidade_numero' => $cpf]);
            }
        }

        // Datas em formato BR (dd/mm/aaaa) -> ISO (Y-m-d) para validação/storage.
        foreach (['data_emissao_rg', 'data_nascimento', 'validade_cnh'] as $key) {
            $v = $this->input($key);
            if (! is_string($v)) {
                continue;
            }
            $v = trim($v);
            if ($v === '') {
                continue;
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) === 1) {
                continue;
            }
            try {
                $dt = Carbon::createFromFormat('d/m/Y', $v);
                $this->merge([$key => $dt->format('Y-m-d')]);
            } catch (\Throwable) {
                // Mantém valor original; rules() lidam com a validação.
            }
        }
    }

    private function normalizarNomePtBr(string $nome): string
    {
        $nome = trim(preg_replace('/\s+/u', ' ', $nome) ?? $nome);
        if ($nome === '') {
            return '';
        }

        $preps = [
            'de', 'da', 'do', 'das', 'dos',
            'e', 'em',
            'para', 'por', 'com', 'sem',
            'na', 'no', 'nas', 'nos',
        ];

        $titleToken = function (string $token) use ($preps): string {
            $parts = preg_split("/([\\-'])/u", $token, -1, PREG_SPLIT_DELIM_CAPTURE);
            if (! is_array($parts)) {
                $parts = [$token];
            }
            $out = '';
            foreach ($parts as $p) {
                if ($p === '-' || $p === "'") {
                    $out .= $p;
                    continue;
                }
                $raw = $p;
                $low = mb_strtolower($p, 'UTF-8');
                if (in_array($low, $preps, true)) {
                    $out .= $low;
                    continue;
                }
                if (preg_match('/^[A-Z0-9]{2,6}$/u', $raw)) {
                    $out .= $raw;
                    continue;
                }
                $out .= mb_convert_case($low, MB_CASE_TITLE, 'UTF-8');
            }
            return $out;
        };

        $tokens = explode(' ', $nome);
        $tokens = array_map(fn ($t) => $t === '' ? '' : $titleToken($t), $tokens);

        return Str::of(implode(' ', $tokens))->trim()->toString();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $empresaId = (int) $this->user()->empresa_id;
        $ufs = array_keys(BrasilEstados::options());

        $base = [
            'tipo_documento' => ['required', Rule::in(['pf', 'pj'])],
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, Closure $fail) {
                    $tipo = $this->input('tipo_documento');
                    $d = DocumentoBrasil::apenasDigitos((string) $value);
                    if ($tipo === 'pf') {
                        if (strlen($d) !== 11 || ! DocumentoBrasil::cpfValido($d)) {
                            $fail(__('CPF inválido.'));
                        }

                        return;
                    }
                    if (strlen($d) !== 14 || ! DocumentoBrasil::cnpjValido($d)) {
                        $fail(__('CNPJ inválido.'));
                    }
                },
                Rule::unique('clientes', 'cpf')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'documento_identidade_tipo' => ['nullable', Rule::in([BrasilOrgaoEmissorDocumento::TIPO_RG, BrasilOrgaoEmissorDocumento::TIPO_CNH, BrasilOrgaoEmissorDocumento::TIPO_CIN])],
            'documento_identidade_numero' => [
                'nullable',
                'string',
                'max:40',
                Rule::requiredIf(fn () => (string) $this->input('tipo_documento') === 'pf' && (string) $this->input('documento_identidade_tipo') === BrasilOrgaoEmissorDocumento::TIPO_RG),
                Rule::requiredIf(fn () => (string) $this->input('tipo_documento') === 'pf' && (string) $this->input('documento_identidade_tipo') === BrasilOrgaoEmissorDocumento::TIPO_CNH),
            ],
            'orgao_emissor' => [
                'nullable',
                'string',
                'max:32',
                Rule::requiredIf(fn () => (string) $this->input('tipo_documento') === 'pf' && in_array((string) $this->input('documento_identidade_tipo'), [BrasilOrgaoEmissorDocumento::TIPO_RG, BrasilOrgaoEmissorDocumento::TIPO_CNH, BrasilOrgaoEmissorDocumento::TIPO_CIN], true)),
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $tipo = (string) $this->input('documento_identidade_tipo');
                    if ($tipo === '') {
                        $fail(__('Selecione o tipo do documento (RG ou CNH).'));
                        return;
                    }
                    if (! in_array($value, BrasilOrgaoEmissorDocumento::valoresPermitidosPara($tipo), true)) {
                        $fail(__('Órgão emissor inválido para o tipo selecionado.'));
                    }
                },
            ],
            'data_emissao_rg' => [
                'nullable',
                'date',
                'before_or_equal:today',
                Rule::requiredIf(fn () => (string) $this->input('tipo_documento') === 'pf' && in_array((string) $this->input('documento_identidade_tipo'), [BrasilOrgaoEmissorDocumento::TIPO_RG, BrasilOrgaoEmissorDocumento::TIPO_CNH, BrasilOrgaoEmissorDocumento::TIPO_CIN], true)),
            ],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            'nome_pai' => ['nullable', 'string', 'max:255'],
            'nome_mae' => ['nullable', 'string', 'max:255'],
            'numero_cnh' => ['nullable', 'string', 'max:32'],
            'categoria_cnh' => ['nullable', 'string', 'max:16'],
            'validade_cnh' => ['nullable', 'date'],
            'primeira_habilitacao' => ['nullable', 'date'],
            'nacionalidade' => ['required', 'string', 'max:100', Rule::in(NacionalidadesComuns::valoresPermitidos())],
            'naturalidade' => ['required', 'string', 'max:100'],
            'cep' => ['required', 'string', 'max:12'],
            'endereco' => ['required', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:120'],
            'cidade' => ['required', 'string', 'max:120'],
            'uf' => ['required', 'string', 'size:2', Rule::in($ufs)],
            'numero' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'apartamento' => ['nullable', 'string', 'max:50'],
            'telefone' => ['required', 'string', 'max:32'],
            'celular' => ['nullable', 'string', 'max:32'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('clientes', 'email')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
        ];

        return array_merge($base, $this->clienteFichaAnexoRules());
    }

    public function messages(): array
    {
        return $this->clienteFichaAnexoMessages();
    }
}
