<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAnexoTipo;
use App\Support\PlatformAnexoTipoContextoModulos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnexoTipoController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = PlatformAnexoTipo::query()
            ->orderBy('ordem')
            ->orderBy('nome');

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo);
            });
        }

        $tipos = $query->get();

        $usosPorTipo = $this->contagensUsoPorTipo($tipos->pluck('id')->all());

        return view('platform.cadastros.anexo-tipos.index', compact('tipos', 'q', 'usosPorTipo'));
    }

    public function create(): View
    {
        return view('platform.cadastros.anexo-tipos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('platform_anexo_tipos', 'slug')],
            'ativo' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'max_size_mb' => ['required', 'integer', 'min:1', 'max:2048'],
            'allowed_mime_types' => ['nullable', 'string', 'max:2000'],
            'allowed_extensions' => ['nullable', 'string', 'max:2000'],
            'is_multiple' => ['nullable', 'boolean'],
            'contexto_modulos' => ['nullable', 'array'],
            'contexto_modulos.*' => ['string', Rule::in(PlatformAnexoTipoContextoModulos::keys())],
        ]);

        $tipo = PlatformAnexoTipo::query()->create([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'ativo' => $request->boolean('ativo', true),
            'ordem' => (int) ($data['ordem'] ?? 0),
            'max_size_mb' => (int) $data['max_size_mb'],
            'allowed_mime_types' => $this->csvToList($data['allowed_mime_types'] ?? null),
            'allowed_extensions' => $this->csvToList($data['allowed_extensions'] ?? null),
            'is_multiple' => $request->boolean('is_multiple', true),
            'contexto_modulos' => $this->normalizeContextoModulos($request),
        ]);

        return redirect()
            ->route('platform.cadastros.anexo-tipos.edit', $tipo)
            ->with('status', __('Tipo criado.'));
    }

    public function edit(PlatformAnexoTipo $anexo_tipo): View
    {
        $tipo = $anexo_tipo;
        $usos = $this->contagensUsoPorTipo([$tipo->id]);
        $counts = $usos[$tipo->id] ?? ['cliente' => 0, 'embarcacao' => 0, 'habilitacao' => 0];
        $modulosComAnexos = PlatformAnexoTipoContextoModulos::keysFromUsoCounts($counts);

        return view('platform.cadastros.anexo-tipos.edit', compact('tipo', 'modulosComAnexos'));
    }

    public function update(Request $request, PlatformAnexoTipo $anexo_tipo): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('platform_anexo_tipos', 'slug')->ignore($anexo_tipo->id)],
            'ativo' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'max_size_mb' => ['required', 'integer', 'min:1', 'max:2048'],
            'allowed_mime_types' => ['nullable', 'string', 'max:2000'],
            'allowed_extensions' => ['nullable', 'string', 'max:2000'],
            'is_multiple' => ['nullable', 'boolean'],
            'contexto_modulos' => ['nullable', 'array'],
            'contexto_modulos.*' => ['string', Rule::in(PlatformAnexoTipoContextoModulos::keys())],
        ]);

        $anexo_tipo->update([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'ativo' => $request->boolean('ativo', true),
            'ordem' => (int) ($data['ordem'] ?? 0),
            'max_size_mb' => (int) $data['max_size_mb'],
            'allowed_mime_types' => $this->csvToList($data['allowed_mime_types'] ?? null),
            'allowed_extensions' => $this->csvToList($data['allowed_extensions'] ?? null),
            'is_multiple' => $request->boolean('is_multiple', true),
            'contexto_modulos' => $this->normalizeContextoModulos($request, $anexo_tipo->id),
        ]);

        return redirect()
            ->route('platform.cadastros.anexo-tipos.index')
            ->with('status', __('Tipo atualizado.'));
    }

    /** @return list<string>|null */
    private function csvToList(?string $csv): ?array
    {
        $csv = trim((string) $csv);
        if ($csv === '') {
            return null;
        }

        $items = array_values(array_filter(array_map(fn ($v) => trim((string) $v), preg_split('/[,\\n]+/', $csv) ?: [])));
        $items = array_values(array_unique(array_filter($items, fn ($v) => $v !== '')));

        return $items === [] ? null : $items;
    }

    /**
     * @return list<string>|null
     */
    private function normalizeContextoModulos(Request $request, ?int $tipoIdParaMesclarUso = null): ?array
    {
        $raw = $request->input('contexto_modulos', []);
        if (! is_array($raw)) {
            $raw = [];
        }

        $allowed = PlatformAnexoTipoContextoModulos::keys();
        $out = [];
        foreach ($raw as $k) {
            if (is_string($k) && in_array($k, $allowed, true)) {
                $out[] = $k;
            }
        }

        if ($tipoIdParaMesclarUso !== null) {
            $usos = $this->contagensUsoPorTipo([$tipoIdParaMesclarUso]);
            $counts = $usos[$tipoIdParaMesclarUso] ?? ['cliente' => 0, 'embarcacao' => 0, 'habilitacao' => 0];
            $out = array_values(array_unique(array_merge(
                $out,
                PlatformAnexoTipoContextoModulos::keysFromUsoCounts($counts)
            )));
        }

        return $out === [] ? null : $out;
    }

    /**
     * Contagens de ficheiros na base por módulo (sem scopes Eloquent — visão global para admin da plataforma).
     *
     * @param  list<int|string>  $tipoIds
     * @return array<int, array{cliente: int, embarcacao: int, habilitacao: int}>
     */
    private function contagensUsoPorTipo(array $tipoIds): array
    {
        $tipoIds = array_values(array_filter(array_map('intval', $tipoIds)));
        if ($tipoIds === []) {
            return [];
        }

        $labels = [
            'cliente_anexos' => 'cliente',
            'embarcacao_anexos' => 'embarcacao',
            'habilitacao_anexos' => 'habilitacao',
        ];

        $out = [];
        foreach ($tipoIds as $id) {
            $out[$id] = ['cliente' => 0, 'embarcacao' => 0, 'habilitacao' => 0];
        }

        foreach ($labels as $table => $key) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'platform_anexo_tipo_id')) {
                continue;
            }
            $rows = DB::table($table)
                ->select('platform_anexo_tipo_id', DB::raw('COUNT(*) as c'))
                ->whereIn('platform_anexo_tipo_id', $tipoIds)
                ->whereNotNull('platform_anexo_tipo_id')
                ->groupBy('platform_anexo_tipo_id')
                ->get();
            foreach ($rows as $row) {
                $tid = (int) $row->platform_anexo_tipo_id;
                if (isset($out[$tid])) {
                    $out[$tid][$key] = (int) $row->c;
                }
            }
        }

        return $out;
    }
}
