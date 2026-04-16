<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Support\PlatformMaintenance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        if ($data['enabled']) {
            PlatformMaintenance::enable();
            $message = __('Modo de manutenção ativado. Os utilizadores da plataforma veem uma página informativa até desativar.');
        } else {
            PlatformMaintenance::disable();
            $message = __('Modo de manutenção desativado. A plataforma voltou ao funcionamento normal.');
        }

        return back()->with('status', $message);
    }
}
