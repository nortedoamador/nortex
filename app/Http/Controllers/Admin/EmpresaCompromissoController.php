<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpresaCompromisso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmpresaCompromissoController extends Controller
{
    private function tipos(): array
    {
        return [
            'reuniao' => __('Reunião'),
            'marinha_atendimento' => __('Atendimento na Marinha'),
            'outro' => __('Outro'),
        ];
    }

    public function index(Request $request): View
    {
        $compromissos = EmpresaCompromisso::query()
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->paginate(20)
            ->withQueryString();

        $tipos = $this->tipos();

        return view('admin.empresa.compromissos.index', compact('compromissos', 'tipos'));
    }

    public function create(): View
    {
        $compromisso = new EmpresaCompromisso(['data' => now()->startOfDay()]);
        $tipos = $this->tipos();

        return view('admin.empresa.compromissos.form', [
            'compromisso' => $compromisso,
            'tipos' => $tipos,
            'tituloPagina' => __('Novo compromisso'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        EmpresaCompromisso::query()->create($data);

        if ($request->input('return_to') === 'empresa_agenda') {
            return redirect()
                ->route('admin.empresa.edit')
                ->with('status', __('Compromisso criado.'))
                ->with('aba_empresa', 'agenda');
        }

        return redirect()
            ->route('admin.empresa.compromissos.index')
            ->with('status', __('Compromisso criado.'));
    }

    public function edit(EmpresaCompromisso $compromisso): View
    {
        $tipos = $this->tipos();

        return view('admin.empresa.compromissos.form', [
            'compromisso' => $compromisso,
            'tipos' => $tipos,
            'tituloPagina' => __('Editar compromisso'),
        ]);
    }

    public function update(Request $request, EmpresaCompromisso $compromisso): RedirectResponse
    {
        $compromisso->update($this->validated($request));

        if ($request->input('return_to') === 'empresa_agenda') {
            return redirect()
                ->route('admin.empresa.edit')
                ->with('status', __('Compromisso atualizado.'))
                ->with('aba_empresa', 'agenda');
        }

        return redirect()
            ->route('admin.empresa.compromissos.index')
            ->with('status', __('Compromisso atualizado.'));
    }

    public function destroy(EmpresaCompromisso $compromisso): RedirectResponse
    {
        $compromisso->delete();

        return redirect()
            ->route('admin.empresa.compromissos.index')
            ->with('status', __('Compromisso removido.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $tiposPermitidos = array_keys($this->tipos());

        $data = $request->validate([
            'tipo' => ['required', 'string', Rule::in($tiposPermitidos)],
            'tipo_custom' => ['nullable', 'string', 'max:128', 'required_if:tipo,outro'],
            'titulo' => ['required', 'string', 'max:255'],
            'data' => ['required', 'date'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fim' => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'local' => ['nullable', 'string', 'max:255'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (($data['tipo'] ?? '') !== 'outro') {
            $data['tipo_custom'] = null;
        } elseif (isset($data['tipo_custom'])) {
            $data['tipo_custom'] = trim((string) $data['tipo_custom']);
        }

        return $data;
    }
}
