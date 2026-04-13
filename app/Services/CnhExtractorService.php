<?php

namespace App\Services;

use App\Services\Cnh\CnhCliPaths;
use App\Services\Cnh\CnhExtractionResult;
use App\Services\Cnh\CnhExtractStageTimer;
use App\Services\Cnh\CnhImagePreprocessor;
use App\Services\Cnh\CnhPayloadNormalizer;
use App\Services\Cnh\CnhPdfRasterizer;
use App\Services\Cnh\CnhPdfTextExtractor;
use App\Services\Cnh\CnhQrZbarReader;
use App\Services\Cnh\CnhTesseractOcr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CnhExtractorService
{
    public function __construct(
        private CnhPayloadNormalizer $normalizer,
        private CnhTesseractOcr $tesseract,
        private CnhPdfTextExtractor $pdfText,
        private CnhPdfRasterizer $pdfRasterizer,
        private CnhImagePreprocessor $preprocessor,
        private CnhQrZbarReader $qrZbar,
    ) {}

    public function extract(UploadedFile $file): CnhExtractionResult
    {
        $timer = new CnhExtractStageTimer;
        $path = $file->getRealPath();
        if ($path === false || ! is_readable($path)) {
            Log::warning('cnh.extract.file_unreadable', $timer->mark());

            return new CnhExtractionResult(
                ok: false,
                source: 'none',
                message: __('Não foi possível ler o arquivo enviado.'),
            );
        }

        $mime = strtolower((string) $file->getMimeType());
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $isPdf = $mime === 'application/pdf' || $ext === 'pdf';

        Log::info('cnh.extract.start', array_merge([
            'mime' => $mime,
            'is_pdf' => $isPdf,
            'basename' => basename((string) $file->getClientOriginalName()),
        ], $timer->mark()));

        if ($isPdf) {
            return $this->extractFromPdf($path, $timer);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/x-png'];
        if (! in_array($mime, $allowed, true)) {
            return new CnhExtractionResult(
                ok: false,
                source: 'none',
                message: __('Envie uma imagem (JPG, PNG ou WEBP) ou um PDF da CNH para leitura automática.'),
            );
        }

        return $this->extractFromUploadedImage($path, $timer);
    }

    private function extractFromPdf(string $absolutePdfPath, CnhExtractStageTimer $timer): CnhExtractionResult
    {
        Log::info('cnh.extract.pdf_loaded', array_merge([
            'basename' => basename($absolutePdfPath),
        ], $timer->mark()));

        $gsPath = trim((string) config('cnh.ghostscript_path', ''));
        if ($gsPath !== '') {
            $gsNorm = CnhCliPaths::normalize($gsPath);
            Log::info('cnh.extract.ghostscript_config', [
                'path' => $gsNorm,
                'file_exists' => is_file($gsNorm),
            ]);
        }

        $raster = $this->pdfRasterizer->rasterizeFirstPage($absolutePdfPath);
        if (! $raster->isSuccess()) {
            Log::warning('cnh.extract.raster_failed', array_merge([
                'message' => $raster->errorMessage,
            ], $timer->mark()));

            return new CnhExtractionResult(
                ok: false,
                source: 'none',
                message: $raster->errorMessage ?? __('Não foi possível processar o PDF da CNH.'),
            );
        }

        $pngPath = $raster->pngPath;
        Log::info('cnh.extract.png_generated', array_merge([
            'bytes' => @filesize($pngPath) ?: 0,
        ], $timer->mark()));

        $procPath = $this->preprocessor->preprocess($pngPath) ?? $pngPath;
        $unlinkPng = $pngPath;
        $unlinkProc = ($procPath !== $pngPath);

        try {
            $fromImage = $this->tryQrThenOcr($procPath, $timer);
            if ($fromImage !== null) {
                return $fromImage;
            }

            Log::info('cnh.extract.pdf_text_fallback', $timer->mark());
            $plain = $this->pdfText->extractText($absolutePdfPath);
            if (is_string($plain) && $plain !== '') {
                $data = $this->normalizer->normalizeFromOcrText($plain);
                if ($this->normalizer->hasMinimumData($data)) {
                    Log::info('cnh.extract.done', array_merge([
                        'source' => 'pdf-text',
                        'total_ms' => $timer->totalMs(),
                    ], $timer->mark()));

                    return new CnhExtractionResult(ok: true, data: $data, source: 'pdf-text');
                }
            }

            Log::warning('cnh.extract.failed', [
                'total_ms' => $timer->totalMs(),
            ]);

            return new CnhExtractionResult(
                ok: false,
                source: 'none',
                message: __('Não foi possível ler automaticamente. Preencha manualmente.'),
            );
        } finally {
            @unlink($unlinkPng);
            if ($unlinkProc) {
                @unlink($procPath);
            }
        }
    }

    private function extractFromUploadedImage(string $absolutePath, CnhExtractStageTimer $timer): CnhExtractionResult
    {
        Log::info('cnh.extract.image_loaded', array_merge([
            'basename' => basename($absolutePath),
        ], $timer->mark()));

        $procPath = $this->preprocessor->preprocess($absolutePath) ?? $absolutePath;
        $unlinkProc = ($procPath !== $absolutePath);

        try {
            $fromImage = $this->tryQrThenOcr($procPath, $timer);
            if ($fromImage !== null) {
                return $fromImage;
            }

            Log::warning('cnh.extract.failed', [
                'total_ms' => $timer->totalMs(),
            ]);

            return new CnhExtractionResult(
                ok: false,
                source: 'none',
                message: __('Não foi possível ler automaticamente. Preencha manualmente.'),
            );
        } finally {
            if ($unlinkProc) {
                @unlink($procPath);
            }
        }
    }

    private function tryQrThenOcr(string $absoluteImagePath, CnhExtractStageTimer $timer): ?CnhExtractionResult
    {
        $tQr = microtime(true);
        $qrRaw = $this->qrZbar->decode($absoluteImagePath);
        $qrMs = round((microtime(true) - $tQr) * 1000, 2);

        Log::info('cnh.extract.qr', array_merge([
            'detected' => $qrRaw !== null && $qrRaw !== '',
            'decode_ms' => $qrMs,
        ], $timer->mark()));

        if (is_string($qrRaw) && $qrRaw !== '') {
            $data = $this->normalizer->normalizeFromQrPayload($qrRaw);
            if ($this->normalizer->hasAnyMeaningfulField($data)) {
                Log::info('cnh.extract.done', array_merge([
                    'source' => 'qr',
                    'total_ms' => $timer->totalMs(),
                ], $timer->mark()));

                return new CnhExtractionResult(ok: true, data: $data, source: 'qr');
            }
        }

        $tOcr = microtime(true);
        Log::info('cnh.extract.ocr_start', $timer->mark());

        $ocrText = $this->tesseract->extractText($absoluteImagePath);
        $ocrMs = round((microtime(true) - $tOcr) * 1000, 2);

        Log::info('cnh.extract.ocr_done', array_merge([
            'chars' => is_string($ocrText) ? strlen($ocrText) : 0,
            'ocr_ms' => $ocrMs,
        ], $timer->mark()));

        if (is_string($ocrText) && $ocrText !== '') {
            $data = $this->normalizer->normalizeFromOcrText($ocrText);
            if ($this->normalizer->hasMinimumData($data)) {
                Log::info('cnh.extract.done', array_merge([
                    'source' => 'ocr',
                    'total_ms' => $timer->totalMs(),
                ], $timer->mark()));

                return new CnhExtractionResult(ok: true, data: $data, source: 'ocr');
            }
        }

        return null;
    }
}
