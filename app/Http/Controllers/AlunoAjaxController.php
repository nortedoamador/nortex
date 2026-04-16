<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EscolaInstrutor;
use App\Support\DocumentoBrasil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlunoAjaxController extends Controller
{
    public function buscarCpf(Request $request): JsonResponse
    {
        $user = $request->user();

        $raw = trim((string) $request->query('q', ''));
        $digits = DocumentoBrasil::apenasDigitos($raw);

        if ($digits === '') {
            return response()->json(['items' => []]);
        }

        $query = Cliente::query()
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('nome')
            ->limit(10);

        // Busca parcial por dígitos do CPF/CNPJ (alguns registros podem estar formatados com pontuação).
        $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $digits).'%';
        $digitsExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),'/',''),' ',''),'(',''),')','')";
        $query->whereRaw("$digitsExpr like ?", [$termo]);

        $items = $query
            ->get(['id', 'nome', 'cpf', 'telefone', 'celular', 'email'])
            ->map(fn (Cliente $c) => [
                'id' => $c->id,
                'nome' => (string) $c->nome,
                'cpf' => (string) $c->cpf,
                'telefone' => (string) ($c->telefone ?? ''),
                'celular' => (string) ($c->celular ?? ''),
                'email' => (string) ($c->email ?? ''),
            ])
            ->all();

        return response()->json(['items' => $items]);
    }

    /**
     * Instrutores já associados à escola (ETN), para pesquisa por CPF ao vincular à aula.
     *
     * @return JsonResponse array{items: list<array{id:int,nome:string,cpf:string,cha:?string}>}
     */
    public function buscarEscolaInstrutorCpf(Request $request): JsonResponse
    {
        $user = $request->user();

        $raw = trim((string) $request->query('q', ''));
        $digits = DocumentoBrasil::apenasDigitos($raw);

        if ($digits === '') {
            return response()->json(['items' => []]);
        }

        $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $digits).'%';
        $digitsExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(clientes.cpf,'.',''),'-',''),'/',''),' ',''),'(',''),')','')";

        $items = EscolaInstrutor::query()
            ->where('empresa_id', $user->empresa_id)
            ->with('cliente:id,nome,cpf')
            ->whereHas('cliente', function ($q) use ($termo, $digitsExpr) {
                $q->whereRaw("$digitsExpr like ?", [$termo]);
            })
            ->limit(50)
            ->get()
            ->sortBy(fn (EscolaInstrutor $e) => mb_strtolower((string) ($e->cliente?->nome ?? '')))
            ->take(10)
            ->values()
            ->map(fn (EscolaInstrutor $e) => [
                'id' => (int) $e->id,
                'nome' => (string) ($e->cliente?->nome ?? ''),
                'cpf' => (string) ($e->cliente?->cpf ?? ''),
                'cha' => $e->cha_numero !== null && $e->cha_numero !== '' ? (string) $e->cha_numero : null,
            ])
            ->all();

        return response()->json(['items' => $items]);
    }

    public function modalStore(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:20'],
            'documento_identidade_numero' => ['nullable', 'string', 'max:40'],
            'orgao_emissor' => ['nullable', 'string', 'max:60'],
            'data_emissao_rg' => ['nullable', 'date'],
            'data_nascimento' => ['nullable', 'date'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'categoria_cnh' => ['nullable', 'string', 'max:10'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:120'],
        ]);

        $cpfDigits = DocumentoBrasil::apenasDigitos((string) $data['cpf']);
        $data['cpf'] = $cpfDigits;

        $exists = Cliente::query()
            ->where('empresa_id', $user->empresa_id)
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),'/',''),' ',''),'(',''),')','') = ?", [$cpfDigits])
            ->exists();

        if ($exists) {
            return response()->json([
                'ok' => false,
                'message' => __('CPF já cadastrado.'),
            ], 422);
        }

        $cliente = Cliente::query()->create(array_merge($data, [
            'empresa_id' => $user->empresa_id,
            'tipo_documento' => 'pf',
        ]));

        return response()->json([
            'ok' => true,
            'item' => [
                'id' => $cliente->id,
                'nome' => (string) $cliente->nome,
                'cpf' => (string) $cliente->cpf,
            ],
        ]);
    }
}

