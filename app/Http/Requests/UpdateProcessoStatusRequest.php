<?php

namespace App\Http\Requests;

use App\Enums\ProcessoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProcessoStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ProcessoStatus::class)],
            'confirmar_ciencia_pendencias_documentais' => ['sometimes', 'boolean'],
        ];
    }
}
