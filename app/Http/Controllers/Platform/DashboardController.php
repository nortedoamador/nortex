<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\PlatformActivityLog;
use App\Models\User;
use App\Support\BrazilStates;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $selectedUf = BrazilStates::normalize($request->query('uf'));
        $empresas = Empresa::query()
            ->select(['id', 'nome', 'ativo', 'uf'])
            ->orderBy('nome')
            ->get();

        $resolvedUfs = $this->resolveEmpresasUf($empresas);
        $empresaIdsByUf = array_fill_keys(BrazilStates::codes(), []);

        foreach ($empresas as $empresa) {
            $uf = $resolvedUfs[$empresa->id] ?? null;

            if ($uf) {
                $empresaIdsByUf[$uf][] = $empresa->id;
            }
        }

        $userCountsByEmpresa = User::query()
            ->selectRaw('empresa_id, count(*) as total')
            ->whereNotNull('empresa_id')
            ->groupBy('empresa_id')
            ->pluck('total', 'empresa_id');

        $mapStats = collect($empresaIdsByUf)
            ->mapWithKeys(function (array $ids, string $uf) use ($userCountsByEmpresa): array {
                $usuarios = collect($ids)->sum(
                    fn (int $empresaId): int => (int) ($userCountsByEmpresa[$empresaId] ?? 0)
                );

                return [$uf => [
                    'empresas' => count($ids),
                    'usuarios' => $usuarios,
                ]];
            })
            ->all();

        $mapCounts = collect($mapStats)
            ->mapWithKeys(fn (array $stats, string $uf) => [$uf => (int) ($stats['empresas'] ?? 0)])
            ->all();

        $filteredEmpresaIds = $selectedUf
            ? ($empresaIdsByUf[$selectedUf] ?? [])
            : $empresas->pluck('id')->all();

        $filteredEmpresas = $selectedUf
            ? $empresas->whereIn('id', $filteredEmpresaIds)->values()
            : $empresas;

        $totEmpresas = $filteredEmpresas->count();
        $totEmpresasAtivas = $filteredEmpresas->where('ativo', true)->count();
        $totUsuarios = $this->countDashboardUsers($selectedUf, $filteredEmpresaIds);
        $totPlatformAdmins = User::query()->where('is_platform_admin', true)->count();

        $ultimosLogsQuery = PlatformActivityLog::query()
            ->with(['user:id,name', 'empresa:id,nome'])
            ->orderByDesc('id')
            ->limit(10);

        if ($selectedUf) {
            if ($filteredEmpresaIds === []) {
                $ultimosLogsQuery->whereRaw('1 = 0');
            } else {
                $ultimosLogsQuery->whereIn('empresa_id', $filteredEmpresaIds);
            }
        }

        $ultimosLogs = $ultimosLogsQuery->get();
        $selectedUfName = $selectedUf ? BrazilStates::label($selectedUf) : 'Brasil';

        return view('platform.dashboard', compact(
            'totEmpresas',
            'totEmpresasAtivas',
            'totUsuarios',
            'totPlatformAdmins',
            'ultimosLogs',
            'mapCounts',
            'mapStats',
            'selectedUf',
            'selectedUfName',
        ));
    }

    /**
     * @param Collection<int, Empresa> $empresas
     * @return array<int, string|null>
     */
    private function resolveEmpresasUf(Collection $empresas): array
    {
        $fallbackUfs = Cliente::query()
            ->selectRaw('empresa_id, upper(uf) as uf, count(*) as total')
            ->whereNotNull('uf')
            ->where('uf', '<>', '')
            ->groupBy('empresa_id', 'uf')
            ->get()
            ->groupBy('empresa_id')
            ->map(fn (Collection $rows) => BrazilStates::normalize(
                (string) $rows->sortByDesc('total')->first()?->uf
            ));

        $resolved = [];

        foreach ($empresas as $empresa) {
            $resolved[$empresa->id] = BrazilStates::normalize($empresa->uf)
                ?? $fallbackUfs->get($empresa->id);
        }

        return $resolved;
    }

    /**
     * @param list<int> $filteredEmpresaIds
     */
    private function countDashboardUsers(?string $selectedUf, array $filteredEmpresaIds): int
    {
        if (! $selectedUf) {
            return User::query()->count();
        }

        if ($filteredEmpresaIds === []) {
            return User::query()
                ->where('is_platform_admin', true)
                ->whereNull('empresa_id')
                ->count();
        }

        return User::query()
            ->where(function ($query) use ($filteredEmpresaIds) {
                $query->whereIn('empresa_id', $filteredEmpresaIds);
                $query->orWhere(function ($platformQuery) {
                    $platformQuery
                        ->where('is_platform_admin', true)
                        ->whereNull('empresa_id');
                });
            })
            ->count();
    }
}

