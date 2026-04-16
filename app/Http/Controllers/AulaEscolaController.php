<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EmpresaAtestadoNormamDuracao;
use App\Models\EscolaCapitania;
use App\Models\EscolaInstrutor;
use App\Models\EscolaNautica;
use App\Models\Habilitacao;
use App\Support\BrasilEstados;
use App\Support\EscolaAutoridadeMaritima;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AulaEscolaController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $escola = EscolaNautica::query()->firstOrCreate(
            ['empresa_id' => $user->empresa_id],
            ['nome' => __('Escola Náutica'), 'cnpj' => null, 'diretor_cliente_id' => null]
        );

        $escola->load(['diretor', 'capitanias']);

        $empresaCnpj = $user->empresa?->cnpj;
        $ufs = BrasilEstados::options();
        $planoTreinamentoCompleto = EmpresaAtestadoNormamDuracao::planoTreinamentoNormamCompleto();

        return view('aulas.escola.edit', compact('escola', 'empresaCnpj', 'ufs', 'planoTreinamentoCompleto'));
    }

    public function instrutores(Request $request): View
    {
        $user = $request->user();
        EscolaNautica::query()->firstOrCreate(
            ['empresa_id' => $user->empresa_id],
            ['nome' => __('Escola Náutica'), 'cnpj' => null, 'diretor_cliente_id' => null]
        );

        $sub = $request->query('sub', 'resumo');
        if (! in_array($sub, ['resumo', 'carteira'], true)) {
            $sub = 'resumo';
        }

        $instrutoresLista = EscolaInstrutor::query()
            ->with('cliente')
            ->orderBy('id')
            ->get();

        return view('aulas.escola.instrutores', compact('instrutoresLista', 'sub'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $escola = EscolaNautica::query()->where('empresa_id', $user->empresa_id)->firstOrFail();

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'diretor_cliente_id' => [
                'nullable',
                'integer',
                Rule::exists(Cliente::class, 'id')->where('empresa_id', $user->empresa_id),
            ],
        ]);

        $escola->update($data);

        return redirect()->route('aulas.escola.edit')->with('status', __('Dados da escola atualizados.'));
    }

    public function storeCapitania(Request $request): RedirectResponse
    {
        $user = $request->user();
        $escola = EscolaNautica::query()->where('empresa_id', $user->empresa_id)->firstOrFail();

        $data = $request->validate([
            'capitania_jurisdicao' => ['nullable', 'string', Rule::in(Habilitacao::JURISDICOES)],
            'capitania_endereco' => ['nullable', 'string', 'max:2000'],
            'representante_funcao' => ['nullable', 'string', Rule::in(EscolaAutoridadeMaritima::FUNCOES)],
            'representante_posto' => ['nullable', 'string', Rule::in(EscolaAutoridadeMaritima::POSTOS)],
            'representante_nome' => ['nullable', 'string', 'max:255'],
        ]);

        $escola->capitanias()->create($data);

        return redirect()->route('aulas.escola.edit')->with('status', __('Capitania adicionada.'));
    }

    public function updateCapitania(Request $request, EscolaCapitania $capitania): RedirectResponse
    {
        $this->assertCapitaniaEmpresa($request, $capitania);

        $data = $request->validate([
            'capitania_jurisdicao' => ['nullable', 'string', Rule::in(Habilitacao::JURISDICOES)],
            'capitania_endereco' => ['nullable', 'string', 'max:2000'],
            'representante_funcao' => ['nullable', 'string', Rule::in(EscolaAutoridadeMaritima::FUNCOES)],
            'representante_posto' => ['nullable', 'string', Rule::in(EscolaAutoridadeMaritima::POSTOS)],
            'representante_nome' => ['nullable', 'string', 'max:255'],
        ]);

        $capitania->update($data);

        return redirect()->route('aulas.escola.edit')->with('status', __('Capitania atualizada.'));
    }

    public function destroyCapitania(Request $request, EscolaCapitania $capitania): RedirectResponse
    {
        $this->assertCapitaniaEmpresa($request, $capitania);
        $capitania->delete();

        return redirect()->route('aulas.escola.edit')->with('status', __('Capitania removida.'));
    }

    private function assertCapitaniaEmpresa(Request $request, EscolaCapitania $capitania): void
    {
        $escola = EscolaNautica::query()->where('empresa_id', $request->user()->empresa_id)->first();
        abort_unless($escola && (int) $capitania->escola_nautica_id === (int) $escola->id, 404);
    }
}
