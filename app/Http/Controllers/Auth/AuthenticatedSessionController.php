<?php

namespace App\Http\Controllers\Auth;

use App\Http\Concerns\RespondsForNorteXAuthSpa;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use RespondsForNorteXAuthSpa;

    /**
     * Display the login view.
     */
    public function create(Request $request): View|JsonResponse
    {
        if ($this->nxAuthSpa($request)) {
            return $this->nxAuthSpaFragment('auth.partials.login-inner', [], 'Entrar');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $default = $this->defaultAfterLoginUrl();

        if ($this->nxAuthSpa($request)) {
            $target = redirect()->intended($default)->getTargetUrl();

            return response()->json(['redirect' => $target]);
        }

        return redirect()->intended($default);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function defaultAfterLoginUrl(): string
    {
        $user = Auth::user();
        if ($user && $user->is_platform_admin && ! $user->empresa_id) {
            return route('platform.empresas.index', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
