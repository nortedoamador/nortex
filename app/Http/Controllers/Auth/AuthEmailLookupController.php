<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthEmailLookupController extends Controller
{
    /**
     * Confirma se o e-mail existe e devolve o nome para exibir no passo da senha.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = Str::lower(trim($validated['email']));

        $name = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->value('name');

        if ($name === null) {
            throw ValidationException::withMessages([
                'email' => 'E-mail não cadastrado.',
            ]);
        }

        return response()->json([
            'name' => $name,
            'email' => $email,
        ]);
    }
}
