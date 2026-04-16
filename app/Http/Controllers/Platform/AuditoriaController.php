<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $action = trim((string) $request->query('action', ''));

        $query = PlatformActivityLog::query()
            ->with(['user:id,name,email', 'impersonator:id,name,email', 'empresa:id,nome,slug'])
            ->orderByDesc('id');

        if ($action !== '') {
            $query->where('action', $action);
        }

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('summary', 'like', $termo)
                    ->orWhere('action', 'like', $termo)
                    ->orWhere('subject_type', 'like', $termo);
            });
        }

        $logs = $query->paginate(30)->withQueryString();

        $acoes = PlatformActivityLog::query()
            ->select('action')
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        if ($action !== '' && ! $acoes->contains($action)) {
            $acoes = $acoes->push($action)->sort()->values();
        }

        return view('platform.auditoria.index', compact('logs', 'q', 'action', 'acoes'));
    }
}
