<?php

namespace App\Http\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait RespondsForNorteXAuthSpa
{
    protected function nxAuthSpa(Request $request): bool
    {
        return $request->header('X-NX-SPA') === '1';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function nxAuthSpaFragment(string $view, array $data, string $title): JsonResponse
    {
        return response()->json([
            'html' => view($view, $data)->render(),
            'title' => $title,
        ]);
    }
}
