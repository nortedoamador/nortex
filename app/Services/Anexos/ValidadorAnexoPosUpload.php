<?php

namespace App\Services\Anexos;

use App\Enums\AnexoValidacaoStatus;
use App\Support\EncryptedS3AnexoStorage;
use App\Support\FileEncryption;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Validação extra após upload: existência, MIME básico e opcionalmente ClamAV.
 */
class ValidadorAnexoPosUpload
{
    /** @var list<string> */
    private const EXTENSOES_PERMITIDAS = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];

    public function validar(
        string $disk,
        string $path,
        ?string $mimeDeclarado,
        ?string $nomeOriginal = null,
        bool $decryptS3Payload = false,
    ): array {
        if (! Storage::disk($disk)->exists($path)) {
            return [
                'status' => AnexoValidacaoStatus::Falhou,
                'notas' => 'Arquivo não encontrado no storage após upload.',
            ];
        }

        $extSource = (EncryptedS3AnexoStorage::isEncryptedDisk($disk) && $decryptS3Payload && $nomeOriginal)
            ? $nomeOriginal
            : $path;
        $ext = strtolower(pathinfo($extSource, PATHINFO_EXTENSION));

        if ($ext !== '' && ! in_array($ext, self::EXTENSOES_PERMITIDAS, true)) {
            return [
                'status' => AnexoValidacaoStatus::Alerta,
                'notas' => 'Extensão fora da lista usual de documentos. Revise manualmente.',
            ];
        }

        if (config('nortex.anexos.clamav_enabled')) {
            $tmpPath = null;
            if (EncryptedS3AnexoStorage::isEncryptedDisk($disk) && $decryptS3Payload) {
                $plain = FileEncryption::decrypt(Storage::disk($disk)->get($path));
                $tmpPath = tempnam(sys_get_temp_dir(), 'nx_clam');
                if ($tmpPath === false) {
                    return [
                        'status' => AnexoValidacaoStatus::Alerta,
                        'notas' => 'Não foi possível criar ficheiro temporário para varredura antivírus.',
                    ];
                }
                file_put_contents($tmpPath, $plain);
                $absoluto = $tmpPath;
            } else {
                $absoluto = Storage::disk($disk)->path($path);
            }

            try {
                return $this->rodarClamAv($absoluto);
            } finally {
                if ($tmpPath !== null && is_file($tmpPath)) {
                    @unlink($tmpPath);
                }
            }
        }

        if ($mimeDeclarado && str_contains($mimeDeclarado, 'octet-stream')) {
            return [
                'status' => AnexoValidacaoStatus::Alerta,
                'notas' => 'MIME genérico (octet-stream). Confirme o tipo real do arquivo.',
            ];
        }

        return [
            'status' => AnexoValidacaoStatus::Ok,
            'notas' => null,
        ];
    }

    /**
     * @return array{status: AnexoValidacaoStatus, notas: string|null}
     */
    private function rodarClamAv(string $absolutePath): array
    {
        $binary = config('nortex.anexos.clamav_binary', 'clamscan');

        try {
            $process = new Process([$binary, '--no-summary', $absolutePath], null, null, null, 120);
            $process->run();

            if (! $process->isSuccessful()) {
                $out = trim($process->getErrorOutput().$process->getOutput());

                return [
                    'status' => AnexoValidacaoStatus::Falhou,
                    'notas' => 'Antivírus: possível detecção ou falha na varredura. '.$out,
                ];
            }
        } catch (\Throwable $e) {
            return [
                'status' => AnexoValidacaoStatus::Alerta,
                'notas' => 'Falha ao executar antivírus: '.$e->getMessage(),
            ];
        }

        return [
            'status' => AnexoValidacaoStatus::Ok,
            'notas' => 'Varredura ClamAV concluída sem alertas.',
        ];
    }
}
