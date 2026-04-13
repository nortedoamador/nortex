<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmpresaSettingsController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    public function edit(Request $request): View
    {
        $empresa = $request->user()->empresa;
        abort_unless($empresa, 404);

        return view('admin.empresa.edit', compact('empresa'));
    }

    public function update(Request $request): RedirectResponse
    {
        $empresa = $request->user()->empresa;
        abort_unless($empresa, 404);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'email_contato' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:32'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $empresa->fill([
            'nome' => $data['nome'],
            'cnpj' => $data['cnpj'] ?? null,
            'email_contato' => $data['email_contato'] ?? null,
            'telefone' => $data['telefone'] ?? null,
        ]);

        if ($request->hasFile('logo')) {
            if ($empresa->logo_path && Storage::disk('public')->exists($empresa->logo_path)) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $path = $request->file('logo')->store('empresas/'.$empresa->id, 'public');
            $empresa->logo_path = $path;
        }

        $empresa->save();

        $this->activityLog->log(
            'empresa_updated',
            __(':actor atualizou os dados da empresa.', ['actor' => $request->user()->name]),
            (int) $empresa->id,
            $empresa::class,
            (int) $empresa->id,
        );

        return redirect()
            ->route('admin.empresa.edit')
            ->with('status', __('Dados da empresa salvos.'));
    }
}
