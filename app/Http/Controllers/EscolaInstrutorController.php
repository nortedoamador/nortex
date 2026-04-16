<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EscolaInstrutor;
use App\Models\Habilitacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EscolaInstrutorController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $this->mergeChaSelectsNullable($request);

        $data = $request->validate([
            'cliente_id' => [
                'required',
                'integer',
                Rule::exists(Cliente::class, 'id')->where('empresa_id', $user->empresa_id),
            ],
            'cha_numero' => ['nullable', 'string', 'max:64'],
            'cha_categoria' => $this->chaCategoriaRules(null),
            'cha_data_emissao' => ['nullable', 'date'],
            'cha_data_validade' => ['nullable', 'date'],
            'cha_jurisdicao' => $this->chaJurisdicaoRules(null),
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

        $this->mergeChaSelectsNullable($request);

        $data = $request->validate([
            'cha_numero' => ['nullable', 'string', 'max:64'],
            'cha_categoria' => $this->chaCategoriaRules($escolaInstrutor),
            'cha_data_emissao' => ['nullable', 'date'],
            'cha_data_validade' => ['nullable', 'date'],
            'cha_jurisdicao' => $this->chaJurisdicaoRules($escolaInstrutor),
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

    private function mergeChaSelectsNullable(Request $request): void
    {
        foreach (['cha_categoria', 'cha_jurisdicao'] as $key) {
            if (! $request->has($key)) {
                continue;
            }
            $v = $request->input($key);
            if ($v === '') {
                $request->merge([$key => null]);
            }
        }
    }

    /**
     * @return array<int, mixed>
     */
    private function chaCategoriaRules(?EscolaInstrutor $existente = null): array
    {
        return [
            'nullable',
            'string',
            function (string $attribute, mixed $value, \Closure $fail) use ($existente): void {
                if ($value === null || $value === '') {
                    return;
                }
                if (! is_string($value)) {
                    $fail(__('validation.string'));

                    return;
                }
                if (in_array($value, Habilitacao::CATEGORIAS_CHA, true)) {
                    return;
                }
                if ($existente !== null && $value === (string) ($existente->cha_categoria ?? '')) {
                    return;
                }
                $fail(__('Categoria CHA inválida.'));
            },
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function chaJurisdicaoRules(?EscolaInstrutor $existente = null): array
    {
        return [
            'nullable',
            'string',
            function (string $attribute, mixed $value, \Closure $fail) use ($existente): void {
                if ($value === null || $value === '') {
                    return;
                }
                if (! is_string($value)) {
                    $fail(__('validation.string'));

                    return;
                }
                if (in_array($value, Habilitacao::JURISDICOES, true)) {
                    return;
                }
                if ($existente !== null && $value === (string) ($existente->cha_jurisdicao ?? '')) {
                    return;
                }
                $fail(__('Jurisdição CHA inválida.'));
            },
        ];
    }
}
