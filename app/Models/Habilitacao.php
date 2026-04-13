<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habilitacao extends TenantModel
{
    protected $table = 'habilitacoes';

    /** @var list<string> */
    public const CATEGORIAS_CHA = [
        'Arrais-Amador',
        'Motonauta',
        'Arrais-Amador e Motonauta',
        'Mestre-Amador',
        'Mestre-Amador e Motonauta',
        'Capitão-Amador',
        'Capitão-Amador e Motonauta',
    ];

    /** @var list<string> */
    public const JURISDICOES = [
        'Agência Fluvial de Boca do Acre',
        'Agência Fluvial de Bom Jesus da Lapa',
        'Agência Fluvial de Caracaraí',
        'Agência Fluvial de Cruzeiro do Sul',
        'Agência Fluvial de Cáceres',
        'Agência Fluvial de Eirunepé',
        'Agência Fluvial de Guajará-Mirim',
        'Agência Fluvial de Humaitá',
        'Agência Fluvial de Imperatriz',
        'Agência Fluvial de Itacoatiara',
        'Agência Fluvial de Parintins',
        'Agência Fluvial de Penedo',
        'Agência Fluvial de Porto Murtinho',
        'Agência Fluvial de Sinop',
        'Agência Fluvial de São Felix do Araguaia',
        'Agência Fluvial de Tefé',
        'Agência da Capitania dos Portos em Aracati',
        'Agência da Capitania dos Portos em Areia Branca',
        'Agência da Capitania dos Portos em Camocim',
        'Agência da Capitania dos Portos em Paraty',
        'Agência da Capitania dos Portos em São João da Barra',
        'Agência da Capitania dos Portos em Tramandaí',
        'Agência da Capitania dos Portos no Oiapoque',
        'Capitania Fluvial da Amazônia Ocidental',
        'Capitania Fluvial de Brasília',
        'Capitania Fluvial de Goiás',
        'Capitania Fluvial de Juazeiro',
        'Capitania Fluvial de Mato Grosso',
        'Capitania Fluvial de Minas Gerais',
        'Capitania Fluvial de Porto Alegre',
        'Capitania Fluvial de Porto Velho',
        'Capitania Fluvial de Santarém',
        'Capitania Fluvial de Tabatinga',
        'Capitania Fluvial do Araguaia Tocantins',
        'Capitania Fluvial do Pantanal',
        'Capitania Fluvial do Rio Paraná',
        'Capitania Fluvial do Tietê-Paraná',
        'Capitania dos Portos da Amazônia Oriental',
        'Capitania dos Portos da Bahia',
        'Capitania dos Portos da Paraíba',
        'Capitania dos Portos de Alagoas',
        'Capitania dos Portos de Macaé',
        'Capitania dos Portos de Pernambuco',
        'Capitania dos Portos de Santa Catarina',
        'Capitania dos Portos de Sergipe',
        'Capitania dos Portos de São Paulo',
        'Capitania dos Portos do Amapá',
        'Capitania dos Portos do Ceará',
        'Capitania dos Portos do Espírito Santo',
        'Capitania dos Portos do Maranhão',
        'Capitania dos Portos do Paraná',
        'Capitania dos Portos do Piauí',
        'Capitania dos Portos do Rio Grande do Norte',
        'Capitania dos Portos do Rio Grande do Sul',
        'Capitania dos Portos do Rio de Janeiro',
        'Centro de Instrucao Almirante Graca Aranha',
        'Centro de Instrução Almirante Braz de Aguiar',
        'Comando do 8º Distrito Naval',
        'Delegacia Fluvial de Furnas',
        'Delegacia Fluvial de Guaíra',
        'Delegacia Fluvial de Pirapora',
        'Delegacia Fluvial de Presidente Epitácio',
        'Delegacia Fluvial de Uruguaiana',
        'Delegacia da Capitania dos Portos em Angra dos Reis',
        'Delegacia da Capitania dos Portos em Cabo Frio',
        'Delegacia da Capitania dos Portos em Ilheus',
        'Delegacia da Capitania dos Portos em Itacuruça',
        'Delegacia da Capitania dos Portos em Itajaí',
        'Delegacia da Capitania dos Portos em Laguna',
        'Delegacia da Capitania dos Portos em Porto Seguro',
        'Delegacia da Capitania dos Portos em São Francisco do Sul',
        'Delegacia da Capitania dos Portos em São Sebastião',
    ];

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'nome',
        'cpf',
        'data_nascimento',
        'numero_cha',
        'categoria',
        'data_emissao',
        'data_validade',
        'jurisdicao',
        'situacao',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'data_emissao' => 'date',
            'data_validade' => 'date',
        ];
    }

    /** Número da CHA sempre em maiúsculas (entrada, armazenamento e leitura). */
    protected function numeroCha(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => ($value === null || $value === '') ? $value : mb_strtoupper($value, 'UTF-8'),
            set: fn (?string $value) => ($value === null || $value === '') ? $value : mb_strtoupper($value, 'UTF-8'),
        );
    }

    /** CPF normalizado (11 dígitos) formatado para exibição. */
    public function cpfFormatadoTitular(): ?string
    {
        if ($this->cpf === null || $this->cpf === '') {
            return null;
        }
        $d = preg_replace('/\D/', '', (string) $this->cpf);
        if (strlen($d) === 11) {
            return substr($d, 0, 3).'.'.substr($d, 3, 3).'.'.substr($d, 6, 3).'-'.substr($d, 9, 2);
        }

        return $this->cpf;
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(HabilitacaoAnexo::class);
    }
}
