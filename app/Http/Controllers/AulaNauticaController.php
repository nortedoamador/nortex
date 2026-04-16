<?php

namespace App\Http\Controllers;

use App\Models\AulaNautica;
use App\Models\Cliente;
use App\Models\EscolaInstrutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AulaNauticaController extends Controller
{
    /**
     * @return Collection<int, array{value:string,label:string}>
     */
    private function tiposAulaOptions(): Collection
    {
        return collect([
            ['value' => 'teorica', 'label' => __('Teórica')],
            ['value' => 'pratica', 'label' => __('Prática')],
            ['value' => 'teorica_pratica', 'label' => __('Teórica e Prática')],
        ]);
    }

    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();

        $busca = trim((string) $request->query('q', ''));
        $qNumero = trim((string) $request->query('numero_oficio', ''));
        $qData = trim((string) $request->query('data', ''));
        $qInstrutor = trim((string) $request->query('instrutor', ''));
        $qAluno = trim((string) $request->query('aluno', ''));
        $qTipoAula = trim((string) $request->query('tipo_aula', ''));

        $query = AulaNautica::query()
            ->where('empresa_id', $user->empresa_id)
            ->withCount(['alunos', 'instrutores', 'escolaInstrutores'])
            ->orderByDesc('data_aula')
            ->orderByDesc('id');

        if ($busca !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $busca).'%';
            $query->where(function ($wq) use ($like) {
                $wq->where('numero_oficio', 'like', $like)
                    ->orWhere('local', 'like', $like)
                    ->orWhereHas('instrutores', function ($iq) use ($like) {
                        $iq->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    })
                    ->orWhereHas('escolaInstrutores.cliente', function ($eq) use ($like) {
                        $eq->where('nome', 'like', $like)
                            ->orWhere('cpf', 'like', $like);
                    })
                    ->orWhereHas('alunos', function ($aq) use ($like) {
                        $aq->where('nome', 'like', $like)
                            ->orWhere('cpf', 'like', $like);
                    });
            });
        }

        if ($qNumero !== '') {
            $query->where('numero_oficio', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], $qNumero).'%');
        }
        if ($qData !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $qData) === 1) {
            $query->whereDate('data_aula', $qData);
        }
        if ($qTipoAula !== '') {
            $allowed = $this->tiposAulaOptions()->pluck('value')->all();
            if (in_array($qTipoAula, $allowed, true)) {
                $query->where('tipo_aula', $qTipoAula);
            }
        }
        if ($qInstrutor !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $qInstrutor).'%';
            $query->where(function ($wq) use ($term) {
                $wq->whereHas('instrutores', function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                })->orWhereHas('escolaInstrutores.cliente', function ($q) use ($term) {
                    $q->where('nome', 'like', $term)
                        ->orWhere('cpf', 'like', $term);
                });
            });
        }
        if ($qAluno !== '') {
            $query->whereHas('alunos', function ($q) use ($qAluno) {
                $q->where('nome', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], $qAluno).'%')
                    ->orWhere('cpf', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], $qAluno).'%');
            });
        }

        $aulas = $query->paginate(15)->withQueryString();

        $tiposAula = $this->tiposAulaOptions();

        if ($request->expectsJson()) {
            return response()->json([
                'rows_html' => view('aulas.partials.index-rows', compact('aulas'))->render(),
                'pagination_html' => $aulas->hasPages() ? (string) $aulas->links() : '',
                'tags_html' => view('aulas.partials.index-filtros-tags', [
                    'busca' => $busca,
                    'qData' => $qData,
                    'qNumero' => $qNumero,
                    'qTipoAula' => $qTipoAula,
                    'qInstrutor' => $qInstrutor,
                    'qAluno' => $qAluno,
                    'tiposAula' => $tiposAula,
                ])->render(),
            ]);
        }

        $escolaInstrutores = $user->hasPermission('aulas.manage')
            ? $this->escolaInstrutoresOptions($user->empresa_id)
            : [];

        $empresaId = (int) $user->empresa_id;

        $kpiAlunosDistintos = (int) DB::table('aula_nautica_alunos')
            ->join('aulas_nauticas', 'aula_nautica_alunos.aula_nautica_id', '=', 'aulas_nauticas.id')
            ->where('aulas_nauticas.empresa_id', $empresaId)
            ->distinct()
            ->count('aula_nautica_alunos.cliente_id');

        $kpiTotalAulas = (int) AulaNautica::query()->where('empresa_id', $empresaId)->count();

        $kpiAtestadosPares = (int) DB::table('empresa_atestado_normam_duracoes')
            ->where('empresa_id', $empresaId)
            ->whereNotNull('duracao_minutos')
            ->count();

        $kpiComunicadosEnviados = (int) AulaNautica::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('comunicado_enviado_em')
            ->count();

        $kpiComunicadosPendentes = max(0, $kpiTotalAulas - $kpiComunicadosEnviados);

        return view('aulas.index', compact(
            'aulas',
            'busca',
            'qNumero',
            'qData',
            'qInstrutor',
            'qAluno',
            'qTipoAula',
            'tiposAula',
            'escolaInstrutores',
            'kpiAlunosDistintos',
            'kpiTotalAulas',
            'kpiAtestadosPares',
            'kpiComunicadosEnviados',
            'kpiComunicadosPendentes'
        ));
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $escolaInstrutores = $this->escolaInstrutoresOptions($user->empresa_id);
        $tiposAula = $this->tiposAulaOptions();

        return view('aulas.form', [
            'aula' => null,
            'escolaInstrutores' => $escolaInstrutores,
            'tiposAula' => $tiposAula,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $this->validated($request, $user->empresa_id);
        $alunosIds = Arr::pull($data, 'alunos_ids', []);
        $escolaInstrutoresIds = Arr::pull($data, 'escola_instrutores_ids', []);

        $aula = AulaNautica::query()->create(array_merge($data, [
            'empresa_id' => $user->empresa_id,
        ]));

        $aula->alunos()->sync($alunosIds);
        $aula->escolaInstrutores()->sync($escolaInstrutoresIds);

        return redirect()->route('aulas.show', $aula)->with('status', __('Aula criada.'));
    }

    public function show(Request $request, AulaNautica $aula): View
    {
        $this->assertEmpresa($request, $aula);

        $aula->load(['alunos', 'instrutores', 'escolaInstrutores.cliente']);

        return view('aulas.show', compact('aula'));
    }

    public function edit(Request $request, AulaNautica $aula): View
    {
        $this->assertEmpresa($request, $aula);

        $aula->load(['alunos', 'instrutores', 'escolaInstrutores.cliente']);
        $escolaInstrutores = $this->escolaInstrutoresOptions($request->user()->empresa_id);
        $tiposAula = $this->tiposAulaOptions();

        return view('aulas.form', compact('aula', 'escolaInstrutores', 'tiposAula'));
    }

    public function update(Request $request, AulaNautica $aula): RedirectResponse
    {
        $this->assertEmpresa($request, $aula);

        $data = $this->validated($request, $request->user()->empresa_id, $aula->id);
        $alunosIds = Arr::pull($data, 'alunos_ids', []);
        $escolaInstrutoresIds = Arr::pull($data, 'escola_instrutores_ids', []);

        $aula->update($data);
        $aula->alunos()->sync($alunosIds);
        $aula->escolaInstrutores()->sync($escolaInstrutoresIds);

        return redirect()->route('aulas.show', $aula)->with('status', __('Aula atualizada.'));
    }

    private function assertEmpresa(Request $request, AulaNautica $aula): void
    {
        abort_unless((int) $aula->empresa_id === (int) $request->user()->empresa_id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, int $empresaId, ?int $ignoreId = null): array
    {
        $tiposAulaAllowed = $this->tiposAulaOptions()->pluck('value')->all();

        return $request->validate([
            'numero_oficio' => [
                'required',
                'string',
                'max:50',
                Rule::unique('aulas_nauticas', 'numero_oficio')
                    ->where('empresa_id', $empresaId)
                    ->ignore($ignoreId),
            ],
            'data_aula' => ['required', 'date'],
            'local' => ['required', 'string', 'max:255'],
            'tipo_aula' => ['required', 'string', Rule::in($tiposAulaAllowed)],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fim' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'status' => ['nullable', 'string', 'max:30'],

            'alunos_ids' => ['nullable', 'array', 'max:200'],
            'alunos_ids.*' => [
                'integer',
                Rule::exists(Cliente::class, 'id')->where('empresa_id', $empresaId),
            ],

            'escola_instrutores_ids' => ['nullable', 'array', 'max:50'],
            'escola_instrutores_ids.*' => [
                'integer',
                Rule::exists(EscolaInstrutor::class, 'id')->where('empresa_id', $empresaId),
            ],
        ]);
    }

    /**
     * @return list<array{id:int,nome:string,cpf:string,cha:?string}>
     */
    private function escolaInstrutoresOptions(int $empresaId): array
    {
        return EscolaInstrutor::query()
            ->with('cliente:id,nome,cpf')
            ->where('empresa_id', $empresaId)
            ->orderBy('id')
            ->get()
            ->map(fn (EscolaInstrutor $e) => [
                'id' => $e->id,
                'nome' => (string) ($e->cliente?->nome ?? '—'),
                'cpf' => (string) ($e->cliente?->cpf ?? ''),
                'cha' => $e->cha_numero,
            ])
            ->all();
    }
}

