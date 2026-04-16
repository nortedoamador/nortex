<?php

namespace App\Http\Controllers;

use App\Models\EmpresaAtestadoNormamDuracao;
use App\Support\AulaCurriculoNormam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AulaAtestadoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->query('tab', 'lista');
        if ($tab === 'alunos') {
            $tab = 'lista';
        }
        if (! in_array($tab, ['lista', 'ara', 'mta'], true)) {
            $tab = 'lista';
        }

        $linhasPorAulaAluno = DB::table('aula_nautica_alunos as ana')
            ->join('aulas_nauticas as an', 'an.id', '=', 'ana.aula_nautica_id')
            ->join('clientes as c', 'c.id', '=', 'ana.cliente_id')
            ->where('an.empresa_id', $user->empresa_id)
            ->orderByDesc('an.data_aula')
            ->orderBy('c.nome')
            ->select([
                'an.id as aula_id',
                'an.numero_oficio',
                'an.data_aula',
                'c.id as cliente_id',
                'c.nome as cliente_nome',
                'c.cpf as cliente_cpf',
            ])
            ->get();

        $duracoesMap = collect();
        if (in_array($tab, ['ara', 'mta'], true)) {
            $programa = $tab === 'mta' ? AulaCurriculoNormam::PROGRAMA_MTA : AulaCurriculoNormam::PROGRAMA_ARA;
            $duracoesMap = EmpresaAtestadoNormamDuracao::query()
                ->where('programa', $programa)
                ->get()
                ->keyBy('item_key');
        }

        $curriculoAra = AulaCurriculoNormam::itensAra();
        $curriculoMta = AulaCurriculoNormam::itensMta();

        return view('aulas.atestados.index', compact(
            'tab',
            'linhasPorAulaAluno',
            'duracoesMap',
            'curriculoAra',
            'curriculoMta'
        ));
    }

    public function storeDurations(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'programa' => ['required', 'string', Rule::in([AulaCurriculoNormam::PROGRAMA_ARA, AulaCurriculoNormam::PROGRAMA_MTA])],
            'duracoes' => ['required', 'array'],
            'duracoes.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $allowed = AulaCurriculoNormam::allKeys($data['programa']);

        foreach ($data['duracoes'] as $key => $minutos) {
            if (! in_array($key, $allowed, true)) {
                continue;
            }
            $val = $minutos === null || $minutos === '' ? null : (int) $minutos;
            EmpresaAtestadoNormamDuracao::query()->updateOrCreate(
                [
                    'empresa_id' => $user->empresa_id,
                    'programa' => $data['programa'],
                    'item_key' => $key,
                ],
                ['duracao_minutos' => $val]
            );
        }

        $tab = $data['programa'] === AulaCurriculoNormam::PROGRAMA_MTA ? 'mta' : 'ara';

        return redirect()->route('aulas.atestados.index', ['tab' => $tab])
            ->with('status', __('Plano de durações guardado.'));
    }
}
