<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AulaNautica;
use App\Models\EmpresaCompromisso;
use App\Services\ActivityLogService;
use App\Support\BrasilEstados;
use App\Support\DocumentoBrasil;
use App\Support\DocumentoModeloTemplateAliases;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $ufs = BrasilEstados::options();
        $textoProcuracaoPadrao = DocumentoModeloTemplateAliases::TEXTO_PADRAO_PROCURACAO_PROCURADORES;

        $iniCal = now()->subMonths(2)->startOfMonth();
        $fimCal = now()->addMonths(4)->endOfMonth();
        $compromissosTipos = [
            'reuniao' => __('Reunião'),
            'marinha_atendimento' => __('Atendimento na Marinha'),
            'outro' => __('Outro'),
        ];
        $compromissoNovoModal = new EmpresaCompromisso(['data' => now()->startOfDay()]);
        $compromissosLinhas = EmpresaCompromisso::query()
            ->whereBetween('data', [$iniCal->toDateString(), $fimCal->toDateString()])
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->get()
            ->map(static function (EmpresaCompromisso $c): array {
                $hi = $c->hora_inicio;
                $horaFmt = null;
                if ($hi instanceof \DateTimeInterface) {
                    $horaFmt = $hi->format('H:i');
                } elseif (is_string($hi) && $hi !== '') {
                    $horaFmt = Str::substr($hi, 0, 5);
                }

                return [
                    'id' => 'compromisso-'.$c->id,
                    'kind' => 'compromisso',
                    'date' => $c->data->format('Y-m-d'),
                    'titulo' => $c->titulo,
                    'tipo' => $c->tipo,
                    'tipo_label' => $c->tipo_label,
                    'hora' => $horaFmt,
                    'url' => route('admin.empresa.compromissos.edit', $c),
                ];
            });

        $aulasLinhas = collect();
        if ($request->user()->hasPermission('aulas.view')) {
            $aulasLinhas = AulaNautica::query()
                ->whereBetween('data_aula', [$iniCal->toDateString(), $fimCal->toDateString()])
                ->whereNotIn('status', ['rascunho', 'cancelada'])
                ->orderBy('data_aula')
                ->orderBy('hora_inicio')
                ->get()
                ->map(static function (AulaNautica $a): array {
                    $hi = $a->hora_inicio;
                    $horaFmt = null;
                    if ($hi instanceof \DateTimeInterface) {
                        $horaFmt = $hi->format('H:i');
                    } elseif (is_string($hi) && $hi !== '') {
                        $horaFmt = Str::substr($hi, 0, 5);
                    }

                    $tipoLabel = match ($a->tipo_aula) {
                        'pratica' => __('Aula prática'),
                        'teorica_pratica' => __('Aula teórica e prática'),
                        default => __('Aula teórica'),
                    };

                    return [
                        'id' => 'aula-'.$a->id,
                        'kind' => 'aula',
                        'date' => $a->data_aula->format('Y-m-d'),
                        'titulo' => $tipoLabel.' · '.__('Of. :n', ['n' => $a->numero_oficio]),
                        'tipo' => 'aula_nautica',
                        'tipo_label' => __('Aula náutica'),
                        'hora' => $horaFmt,
                        'url' => route('aulas.show', $a),
                    ];
                });
        }

        $compromissosAgendaPayload = $compromissosLinhas
            ->concat($aulasLinhas)
            ->sort(static function (array $a, array $b): int {
                $cmp = strcmp($a['date'], $b['date']);
                if ($cmp !== 0) {
                    return $cmp;
                }

                return strcmp($a['hora'] ?? '', $b['hora'] ?? '');
            })
            ->values()
            ->all();

        return view('admin.empresa.edit', compact(
            'empresa',
            'ufs',
            'textoProcuracaoPadrao',
            'compromissosAgendaPayload',
            'compromissosTipos',
            'compromissoNovoModal',
        ));
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
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
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
            'uf' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $v = is_string($value) ? trim($value) : '';
                    if ($v === '') {
                        return;
                    }
                    if (strlen($v) !== 2 || ! array_key_exists($v, BrasilEstados::options())) {
                        $fail(__('UF inválida.'));
                    }
                },
            ],
            'cidade' => ['nullable', 'string', 'max:120'],
            'cep' => [
                'nullable',
                'string',
                'max:12',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return;
                    }
                    $digits = DocumentoBrasil::apenasDigitos($raw);
                    if (strlen($digits) !== 8) {
                        $fail(__('CEP inválido.'));
                    }
                },
            ],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:32'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'bairro' => ['nullable', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'texto_procuracao_procuradores' => ['nullable', 'string', 'max:65535'],
        ]);

        $cnpj = trim((string) ($data['cnpj'] ?? ''));
        $telefone = trim((string) ($data['telefone'] ?? ''));

        $ufRaw = isset($data['uf']) ? strtoupper(trim((string) $data['uf'])) : '';
        $cidadeRaw = isset($data['cidade']) ? trim((string) $data['cidade']) : '';
        $nomeFantasiaRaw = isset($data['nome_fantasia']) ? trim((string) $data['nome_fantasia']) : '';
        $cepRaw = isset($data['cep']) ? trim((string) $data['cep']) : '';
        $enderecoRaw = isset($data['endereco']) ? trim((string) $data['endereco']) : '';
        $numeroRaw = isset($data['numero']) ? trim((string) $data['numero']) : '';
        $complementoRaw = isset($data['complemento']) ? trim((string) $data['complemento']) : '';
        $bairroRaw = isset($data['bairro']) ? trim((string) $data['bairro']) : '';
        $textoProcRaw = isset($data['texto_procuracao_procuradores'])
            ? trim((string) $data['texto_procuracao_procuradores'])
            : '';
        if ($textoProcRaw === '' || $textoProcRaw === DocumentoModeloTemplateAliases::TEXTO_PADRAO_PROCURACAO_PROCURADORES) {
            $textoProcuracaoProcuradores = null;
        } else {
            $textoProcuracaoProcuradores = str_replace("\r\n", "\n", $textoProcRaw);
        }

        $empresa->fill([
            'nome' => $data['nome'],
            'nome_fantasia' => $nomeFantasiaRaw !== '' ? $nomeFantasiaRaw : null,
            'cnpj' => $cnpj !== '' ? DocumentoBrasil::formatarCnpj($cnpj) : null,
            'email_contato' => $data['email_contato'] ?? null,
            'telefone' => $telefone !== '' ? self::formatarTelefoneBr($telefone) : null,
            'uf' => $ufRaw !== '' ? $ufRaw : null,
            'cidade' => $cidadeRaw !== '' ? $cidadeRaw : null,
            'cep' => $cepRaw !== '' ? self::formatarCepBr($cepRaw) : null,
            'endereco' => $enderecoRaw !== '' ? $enderecoRaw : null,
            'numero' => $numeroRaw !== '' ? $numeroRaw : null,
            'complemento' => $complementoRaw !== '' ? $complementoRaw : null,
            'bairro' => $bairroRaw !== '' ? $bairroRaw : null,
            'texto_procuracao_procuradores' => $textoProcuracaoProcuradores,
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

    private static function formatarCepBr(string $valor): string
    {
        $digits = DocumentoBrasil::apenasDigitos($valor);
        if (strlen($digits) === 8) {
            return substr($digits, 0, 5).'-'.substr($digits, 5);
        }

        return $valor;
    }
}
