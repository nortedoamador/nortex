<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EscolaInstrutor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EscolaInstrutorController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'cliente_id' => [
                'required',
                'integer',
                Rule::exists(Cliente::class, 'id')->where('empresa_id', $user->empresa_id),
            ],
            'cha_numero' => ['nullable', 'string', 'max:64'],
            'cha_categoria' => ['nullable', 'string', 'max:80'],
            'cha_data_emissao' => ['nullable', 'date'],
            'cha_data_validade' => ['nullable', 'date'],
            'cha_jurisdicao' => ['nullable', 'string', 'max:255'],
        ]);

        $instrutor = EscolaInstrutor::query()->updateOrCreate(
            [
                'empresa_id' => $user->empresa_id,
                'cliente_id' => $data['cliente_id'],
            ],
            [
                'cha_numero' => $data['cha_numero'] ?? null,
                'cha_categoria' => $data['cha_categoria'] ?? null,
                'cha_data_emissao' => $data['cha_data_emissao'] ?? null,
                'cha_data_validade' => $data['cha_data_validade'] ?? null,
                'cha_jurisdicao' => $data['cha_jurisdicao'] ?? null,
            ]
        );

        $instrutor->load('cliente:id,nome,cpf');

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'instrutor' => [
                    'id' => $instrutor->id,
                    'cliente_id' => $instrutor->cliente_id,
                    'nome' => (string) ($instrutor->cliente?->nome ?? ''),
                    'cpf' => (string) ($instrutor->cliente?->cpf ?? ''),
                ],
            ]);
        }

        return back()->with('status', __('Instrutor da escola registado.'));
    }

    public function update(Request $request, EscolaInstrutor $escolaInstrutor): RedirectResponse
    {
        $this->assertEmpresa($request, $escolaInstrutor);

        $data = $request->validate([
            'cha_numero' => ['nullable', 'string', 'max:64'],
            'cha_categoria' => ['nullable', 'string', 'max:80'],
            'cha_data_emissao' => ['nullable', 'date'],
            'cha_data_validade' => ['nullable', 'date'],
            'cha_jurisdicao' => ['nullable', 'string', 'max:255'],
        ]);

        $escolaInstrutor->update($data);

        return redirect()->route('aulas.escola.instrutores', ['sub' => 'carteira'])->with('status', __('Dados do instrutor atualizados.'));
    }

    public function destroy(Request $request, EscolaInstrutor $escolaInstrutor): RedirectResponse
    {
        $this->assertEmpresa($request, $escolaInstrutor);
        $escolaInstrutor->delete();

        return redirect()->route('aulas.escola.instrutores', ['sub' => 'carteira'])->with('status', __('Instrutor removido da escola.'));
    }

    private function assertEmpresa(Request $request, EscolaInstrutor $instrutor): void
    {
        abort_unless((int) $instrutor->empresa_id === (int) $request->user()->empresa_id, 404);
    }
}
