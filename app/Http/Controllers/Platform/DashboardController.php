<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\PlatformActivityLog;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totEmpresas = Empresa::query()->count();
        $totEmpresasAtivas = Empresa::query()->where('ativo', true)->count();
        $totUsuarios = User::query()->count();
        $totPlatformAdmins = User::query()->where('is_platform_admin', true)->count();

        $ultimosLogs = PlatformActivityLog::query()
            ->with(['user:id,name', 'empresa:id,nome'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('platform.dashboard', compact(
            'totEmpresas',
            'totEmpresasAtivas',
            'totUsuarios',
            'totPlatformAdmins',
            'ultimosLogs',
        ));
    }
}

