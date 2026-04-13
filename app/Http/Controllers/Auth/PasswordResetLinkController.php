<?php

namespace App\Http\Controllers\Auth;

use App\Http\Concerns\RespondsForNorteXAuthSpa;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    use RespondsForNorteXAuthSpa;

    /**
     * Display the password reset link request view.
     */
    public function create(Request $request): View|JsonResponse
    {
        if ($this->nxAuthSpa($request)) {
            return $this->nxAuthSpaFragment('auth.partials.forgot-inner', [], 'Recuperar senha');
        }

        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            if ($this->nxAuthSpa($request)) {
                $request->session()->flash('status', __($status));

                return response()->json(['refetch' => true]);
            }

            return back()->with('status', __($status));
        }

        if ($this->nxAuthSpa($request)) {
            return response()->json([
                'message' => __($status),
                'errors' => ['email' => [__($status)]],
            ], 422);
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
