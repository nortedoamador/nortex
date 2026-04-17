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
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedSessionController extends Controller
{
    use RespondsForNorteXAuthSpa;

    /**
     * Display the login view.
     */
    public function create(Request $request): Response|JsonResponse
    {
        if ($this->nxAuthSpa($request)) {
            return $this->nxAuthSpaFragment('auth.partials.login-inner', [], 'Entrar');
        }

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
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

        return redirect()
            ->route('login')
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
    }

    private function defaultAfterLoginUrl(): string
    {
        $user = Auth::user();
        if ($user && ($user->is_platform_admin || $user->is_master_admin) && ! $user->empresa_id) {
            return route('platform.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
