<?php

namespace App\Services\Anexos;

use App\Enums\AnexoValidacaoStatus;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Validação extra após upload: existência, MIME básico e opcionalmente ClamAV.
 */
class ValidadorAnexoPosUpload
{
    /** @var list<string> */
    private const EXTENSOES_PERMITIDAS = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];

    public function validar(string $disk, string $path, ?string $mimeDeclarado): array
    {
        if (! Storage::disk($disk)->exists($path)) {
            return [
                'status' => AnexoValidacaoStatus::Falhou,
                'notas' => 'Arquivo não encontrado no storage após upload.',
            ];
        }

        $absoluto = Storage::disk($disk)->path($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext !== '' && ! in_array($ext, self::EXTENSOES_PERMITIDAS, true)) {
            return [
                'status' => AnexoValidacaoStatus::Alerta,
                'notas' => 'Extensão fora da lista usual de documentos. Revise manualmente.',
            ];
        }

        if (config('nortex.anexos.clamav_enabled')) {
            return $this->rodarClamAv($absoluto);
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
