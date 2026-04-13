<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\CnhExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CnhExtractController extends Controller
{
    public function __invoke(Request $request, CnhExtractorService $extractor): JsonResponse
    {
        $this->authorize('create', Cliente::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $result = $extractor->extract($validated['file']);

        $status = $result->ok ? 200 : 422;

        return response()->json([
            'ok' => $result->ok,
            'source' => $result->source,
            'data' => $result->data,
            'message' => $result->message,
        ], $status);
    }
}
