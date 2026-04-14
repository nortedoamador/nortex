<?php

namespace App\Jobs;

use App\Enums\AnexoValidacaoStatus;
use App\Support\EncryptedS3AnexoStorage;
use App\Services\Anexos\ValidadorAnexoPosUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class ValidarAnexoUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $modelClass,
        public int $anexoId,
    ) {}

    public function handle(ValidadorAnexoPosUpload $validador): void
    {
        if (! class_exists($this->modelClass)) {
            return;
        }

        /** @var Model|null $model */
        $model = $this->modelClass::query()->withoutGlobalScopes()->find($this->anexoId);

        if (! $model || ! $model->getAttribute('path')) {
            return;
        }

        $disk = (string) $model->getAttribute('disk');
        $path = (string) $model->getAttribute('path');
        $mime = $model->getAttribute('mime');
        $nomeOriginal = $model->getAttribute('nome_original');
        $decryptS3Payload = EncryptedS3AnexoStorage::isEncryptedDisk($disk);

        $r = $validador->validar(
            $disk,
            $path,
            $mime ? (string) $mime : null,
            $nomeOriginal ? (string) $nomeOriginal : null,
            $decryptS3Payload,
        );

        $model->forceFill([
            'extra_validation_status' => $r['status']->value,
            'extra_validation_notes' => $r['notas'],
            'validated_at' => now(),
        ])->saveQuietly();
    }
}
