<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cliente extends TenantModel
{
    protected $fillable = [
        'empresa_id',
        'nome',
        'cpf',
        'data_nascimento',
        'tipo_documento',
        'rg',
        'documento_identidade_tipo',
        'documento_identidade_numero',
        'numero_cnh',
        'categoria_cnh',
        'validade_cnh',
        'primeira_habilitacao',
        'orgao_emissor',
        'data_emissao_rg',
        'nacionalidade',
        'naturalidade',
        'nome_pai',
        'nome_mae',
        'cep',
        'endereco',
        'bairro',
        'cidade',
        'uf',
        'numero',
        'complemento',
        'apartamento',
        'telefone',
        'celular',
        'email',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'data_emissao_rg' => 'date',
            'validade_cnh' => 'date',
            'primeira_habilitacao' => 'date',
        ];
    }

    public function iniciaisAvatar(): string
    {
        $nome = trim((string) $this->nome);
        if ($nome === '') {
            return '?';
        }

        return Str::upper(Str::substr($nome, 0, 1));
    }

    /** CPF ou CNPJ formatado para exibição (coluna `cpf`). */
    public function documentoFormatado(): ?string
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

    public function cpfFormatado(): ?string
    {
        return $this->documentoFormatado();
    }

    public function cepFormatado(): ?string
    {
        if ($this->cep === null || $this->cep === '') {
            return null;
        }
        $d = preg_replace('/\D/', '', $this->cep);
        if (strlen($d) === 8) {
            return substr($d, 0, 5).'-'.substr($d, 5);
        }

        return $this->cep;
    }

    public function telefoneFormatado(): ?string
    {
        return $this->formatarTelefoneBr($this->telefone);
    }

    public function celularFormatado(): ?string
    {
        return $this->formatarTelefoneBr($this->celular);
    }

    private function formatarTelefoneBr(?string $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        $d = preg_replace('/\D/', '', $valor);

        return match (strlen($d)) {
            11 => sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 5), substr($d, 7)),
            10 => sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 4), substr($d, 6)),
            default => $valor,
        };
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(ClienteAnexo::class);
    }

    public function embarcacoes(): HasMany
    {
        return $this->hasMany(Embarcacao::class);
    }

    public function habilitacoes(): HasMany
    {
        return $this->hasMany(Habilitacao::class);
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }
}
