<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\TenantEmpresaContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $acao = $request->query('acao');
        $acao = is_string($acao) && $acao !== '' ? $acao : null;

        $tenantActorScope = function ($uq): void {
            $uq->where('is_platform_admin', false)->where('is_master_admin', false);
        };

        $logs = ActivityLog::query()
            ->where('empresa_id', $empresaId)
            ->whereHas('user', $tenantActorScope)
            ->with('user:id,name,is_platform_admin,is_master_admin')
            ->when($acao, fn ($q) => $q->where('action', $acao))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $acoes = ActivityLog::query()
            ->where('empresa_id', $empresaId)
            ->whereHas('user', $tenantActorScope)
            ->selectRaw('action, COUNT(*) as c')
            ->groupBy('action')
            ->orderBy('action')
            ->pluck('c', 'action');

        return view('admin.auditoria.index', compact('logs', 'acao', 'acoes'));
    }
}
