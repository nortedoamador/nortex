<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Support\DocumentoBrasil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

    public function logo(Request $request): BinaryFileResponse
    {
        $empresa = $request->user()->empresa;
        abort_unless($empresa && filled($empresa->logo_path), 404);
        abort_unless(Storage::disk('public')->exists($empresa->logo_path), 404);

        return response()->file(Storage::disk('public')->path($empresa->logo_path));
    }

    public function update(Request $request): RedirectResponse
    {
        $empresa = $request->user()->empresa;
        abort_unless($empresa, 404);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return;
                    }

                    $digits = DocumentoBrasil::apenasDigitos($raw);
                    if (strlen($digits) !== 14 || ! DocumentoBrasil::cnpjValido($digits)) {
                        $fail(__('CNPJ inválido.'));
                    }
                },
            ],
            'email_contato' => ['nullable', 'string', 'email', 'max:255'],
            'telefone' => [
                'nullable',
                'string',
                'max:32',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return;
                    }

                    $digits = DocumentoBrasil::apenasDigitos($raw);
                    if (! in_array(strlen($digits), [10, 11], true)) {
                        $fail(__('Telefone inválido.'));
                    }
                },
            ],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $cnpj = trim((string) ($data['cnpj'] ?? ''));
        $telefone = trim((string) ($data['telefone'] ?? ''));

        $empresa->fill([
            'nome' => $data['nome'],
            'cnpj' => $cnpj !== '' ? DocumentoBrasil::formatarCnpj($cnpj) : null,
            'email_contato' => $data['email_contato'] ?? null,
            'telefone' => $telefone !== '' ? self::formatarTelefoneBr($telefone) : null,
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

    private static function formatarTelefoneBr(string $valor): string
    {
        $digits = DocumentoBrasil::apenasDigitos($valor);

        if (strlen($digits) === 11) {
            return '('.substr($digits, 0, 2).') '.substr($digits, 2, 5).'-'.substr($digits, 7, 4);
        }

        if (strlen($digits) === 10) {
            return '('.substr($digits, 0, 2).') '.substr($digits, 2, 4).'-'.substr($digits, 6, 4);
        }

        return $valor;
    }
}
