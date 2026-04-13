<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Embarcacao;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DemoClientesEmbarcacoesSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->where('slug', 'demo')->first();
        if (! $empresa) {
            return;
        }

        $user = User::query()->where('email', 'admin@nortex.local')->first();
        if ($user) {
            Auth::login($user);
        }

        $clientesBase = [
            ['nome' => 'Carlos Eduardo Silva', 'cpf' => '529.982.247-25', 'email' => 'carlos@email.com', 'cidade' => 'Rio de Janeiro', 'uf' => 'RJ', 'celular' => '21998765432'],
            ['nome' => 'Mariana Oliveira', 'cpf' => '390.533.447-05', 'email' => 'mariana@email.com', 'cidade' => 'Niterói', 'uf' => 'RJ', 'celular' => '21991234567'],
            ['nome' => 'João Pedro Souza', 'cpf' => '168.995.350-09', 'email' => 'joao@email.com', 'cidade' => 'São Paulo', 'uf' => 'SP', 'celular' => '11987654321'],
            ['nome' => 'Ana Beatriz Lima', 'cpf' => '987.654.321-00', 'email' => 'ana@email.com', 'cidade' => 'Salvador', 'uf' => 'BA', 'celular' => '71999990000'],
            ['nome' => 'Empresa Atlântica LTDA', 'cpf' => '11.222.333/0001-81', 'email' => 'contato@atlantica.test', 'tipo_documento' => 'pj'],
        ];

        $clientes = collect();
        foreach ($clientesBase as $c) {
            $email = $c['email'] ?? null;
            $attrs = [
                'empresa_id' => $empresa->id,
                'nome' => $c['nome'],
            ];
            if ($email) {
                $attrs['email'] = $email;
            }
            if (! empty($c['cpf'])) {
                $attrs['cpf'] = $c['cpf'];
            }

            $cliente = Cliente::query()->firstOrCreate(
                ['empresa_id' => $empresa->id, 'email' => $email],
                $attrs,
            );

            // completa alguns campos para telas
            $cliente->fill([
                'tipo_documento' => $c['tipo_documento'] ?? 'pf',
                'cidade' => $c['cidade'] ?? $cliente->cidade,
                'uf' => $c['uf'] ?? $cliente->uf,
                'celular' => $c['celular'] ?? $cliente->celular,
                'telefone' => $cliente->telefone ?: '2133334444',
                'nacionalidade' => $cliente->nacionalidade ?: 'Brasileira',
                'naturalidade' => $cliente->naturalidade ?: ($c['cidade'] ?? 'Rio de Janeiro'),
                'cep' => $cliente->cep ?: '20040-020',
                'endereco' => $cliente->endereco ?: 'Av. Atlântica',
                'bairro' => $cliente->bairro ?: 'Centro',
                'numero' => $cliente->numero ?: '100',
            ])->save();

            $clientes->push($cliente);
        }

        $materiais = ['Madeira', 'Alumínio', 'Aço', 'Fibra de Vidro', 'Fibra de Carbono', 'Kevlar', 'Polietileno', 'Borracha', 'Outros'];
        $atividades = ['Esporte e Recreio', 'Transporte de Passageiros', 'Transporte de Carga', 'Transporte de Passageiros e Carga'];
        $tipos = [
            'Moto-Aquática/similar',
            'Lancha',
            'Iate',
            'Jet Boat',
            'Multicasco (Catamarã, Trimarã, Tetramarã, etc)',
            'Bote',
            'Escuna',
            'Pesqueiro',
            'Outros',
        ];
        $marcas = ['Yamaha', 'Mercury', 'Suzuki', 'Honda Marine', 'Volvo Penta', 'Cummins Marine', 'Yanmar', 'Outros'];

        $embarcacoesBase = [
            ['nome' => 'Wave Runner Azul', 'tipo' => 'Moto-Aquática/similar', 'inscricao' => null],
            ['nome' => 'Lancha Sol Nascente', 'tipo' => 'Lancha', 'inscricao' => 'RJ-123456'],
            ['nome' => 'Iate Ventos do Sul', 'tipo' => 'Iate', 'inscricao' => 'SP-987654'],
            ['nome' => 'Catamarã Mar Aberto', 'tipo' => 'Multicasco (Catamarã, Trimarã, Tetramarã, etc)', 'inscricao' => null],
            ['nome' => 'Pesqueiro Bom Peixe', 'tipo' => 'Pesqueiro', 'inscricao' => 'BA-555888'],
            ['nome' => 'Bote Apoio 01', 'tipo' => 'Bote', 'inscricao' => null],
        ];

        foreach ($embarcacoesBase as $i => $e) {
            $cliente = $clientes->get($i % max(1, $clientes->count()));

            Embarcacao::query()->firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $e['nome']],
                [
                    'cliente_id' => $cliente?->id,
                    'cpf' => $cliente?->cpf,
                    'inscricao' => $e['inscricao'],
                    'tipo' => $e['tipo'],
                    'atividade' => $atividades[$i % count($atividades)],
                    'material_casco' => $materiais[$i % count($materiais)],
                    'numero_casco' => 'CASCO-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'construtor' => 'Construtora Naval '.chr(65 + $i),
                    'cor_casco_ficha' => ['Branco', 'Azul', 'Cinza', 'Preto'][$i % 4],
                    'ano_construcao' => 2015 + ($i % 10),
                    'arqueacao_bruta' => (string) (10 + $i * 2),
                    'arqueacao_liquida' => (string) (8 + $i * 2),
                    'tripulantes' => 2 + ($i % 4),
                    'passageiros' => 6 + ($i % 10),
                    'marca_motor' => $marcas[$i % count($marcas)],
                    'potencia_maxima_motor' => (string) (150 + $i * 25).' HP',
                    'numero_motor' => 'MOTOR-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'comprimento' => number_format(8.5 + $i, 2, ',', '.'),
                    'boca' => number_format(2.6 + ($i * 0.1), 2, ',', '.'),
                    'pontal' => number_format(1.2 + ($i * 0.05), 2, ',', '.'),
                    'calado' => number_format(0.8 + ($i * 0.03), 2, ',', '.'),
                    'contorno' => number_format(2.5 + ($i * 0.1), 2, ',', '.'),
                ],
            );
        }

        Auth::logout();
    }
}

