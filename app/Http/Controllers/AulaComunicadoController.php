<?php

namespace App\Http\Controllers;

use App\Models\AulaNautica;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AulaComunicadoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $aulas = AulaNautica::query()
            ->where('empresa_id', $user->empresa_id)
            ->orderByDesc('data_aula')
            ->orderByDesc('id')
            ->get(['id', 'numero_oficio', 'data_aula', 'local', 'comunicado_enviado_em']);

        return view('aulas.comunicados.index', compact('aulas'));
    }

    public function markEnviado(Request $request, AulaNautica $aula): RedirectResponse
    {
        abort_unless((int) $aula->empresa_id === (int) $request->user()->empresa_id, 404);

        $request->validate([
            'enviado' => ['required', 'boolean'],
        ]);

        $aula->comunicado_enviado_em = $request->boolean('enviado') ? now() : null;
        $aula->save();

        return redirect()->route('aulas.comunicados.index')->with('status', __('Estado do comunicado atualizado.'));
    }
}
