<?php



namespace App\Services\Cnh;



use Illuminate\Support\Facades\Log;

use Symfony\Component\Process\Process;

use Throwable;



/**

 * Converte a 1.ª página do PDF em PNG (Imagick + Ghostscript, ou só Ghostscript em CLI).

 * 300 DPI, fundo branco, sem transparência, nitidez inicial.

 */

final class CnhPdfRasterizer

{

    public function rasterizeFirstPage(string $absolutePdfPath): CnhRasterizeResult

    {

        if (! is_readable($absolutePdfPath)) {

            return new CnhRasterizeResult(null, __('Não foi possível ler o ficheiro PDF.'));

        }



        $failConvert = __('Não foi possível converter o PDF em imagem. Defina CNH_GHOSTSCRIPT_PATH no .env com o caminho completo de gswin64c.exe (pasta bin do Ghostscript) ou adicione essa pasta ao PATH do Windows; confira também a política PDF do ImageMagick (policy.xml).');

        $noImagick = __('Extensão PHP Imagick não está carregada. Instale php_imagick para o PHP usado pelo servidor web (ex.: XAMPP) e reinicie o Apache.');



        $this->applyGhostscriptEnvFromConfig();



        $imagickOk = extension_loaded('imagick');

        if ($imagickOk) {

            $fromIm = $this->rasterizeWithImagick($absolutePdfPath, $failConvert);

            if ($fromIm->isSuccess()) {

                return $fromIm;

            }

            Log::info('cnh.pdf.raster_imagick_failed_trying_gs_cli');

        }



        $fromGs = $this->rasterizeViaGhostscriptCli($absolutePdfPath, $failConvert);

        if ($fromGs->isSuccess()) {

            return $fromGs;

        }



        if (! $imagickOk && $this->resolveGhostscriptExecutable() === null) {

            return new CnhRasterizeResult(null, $noImagick);

        }



        return new CnhRasterizeResult(null, $failConvert);

    }



    private function rasterizeWithImagick(string $absolutePdfPath, string $failConvert): CnhRasterizeResult

    {

        try {

            $im = new \Imagick;

            $im->setResolution(300, 300);



            $white = new \ImagickPixel('white');

            $im->setBackgroundColor($white);

            $im->setImageBackgroundColor($white);



            $im->readImage($absolutePdfPath.'[0]');

            $im->setImageFormat('png');



            if (defined('Imagick::ALPHACHANNEL_REMOVE')) {

                $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);

            }



            if (defined('Imagick::LAYERMETHOD_FLATTEN')) {

                $flat = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

                $im->clear();

                $im->destroy();

                $im = $flat;

            }



            $im->sharpenImage(0, 1.0);



            $base = tempnam(sys_get_temp_dir(), 'nxpdf');

            if ($base === false) {

                $im->clear();

                $im->destroy();



                return new CnhRasterizeResult(null, $failConvert);

            }

            @unlink($base);

            $tmp = $base.'.png';

            $im->writeImage($tmp);

            $im->clear();

            $im->destroy();



            if (! is_readable($tmp) || filesize($tmp) === 0) {

                @unlink($tmp);



                return new CnhRasterizeResult(null, $failConvert);

            }



            return new CnhRasterizeResult($tmp, null);

        } catch (Throwable $e) {

            Log::warning('cnh.pdf.raster_imagick_exception', [

                'message' => $e->getMessage(),

            ]);



            return new CnhRasterizeResult(null, $failConvert);

        }

    }



    /**

     * Contorna bloqueios comuns do delegate PDF do ImageMagick (ex.: policy.xml no Windows).

     */

    private function rasterizeViaGhostscriptCli(string $absolutePdfPath, string $failConvert): CnhRasterizeResult

    {

        $exe = $this->resolveGhostscriptExecutable();

        if ($exe === null) {

            return new CnhRasterizeResult(null, $failConvert);

        }



        $base = tempnam(sys_get_temp_dir(), 'nxgspdf');

        if ($base === false) {

            return new CnhRasterizeResult(null, $failConvert);

        }

        @unlink($base);

        $tmp = $base.'.png';



        $pdfArg = str_replace('\\', '/', $absolutePdfPath);

        $outArg = str_replace('\\', '/', $tmp);



        try {

            $process = new Process([

                $exe,

                '-dNOPAUSE',

                '-dBATCH',

                '-dSAFER',

                '-dQUIET',

                '-sDEVICE=png16m',

                '-r300',

                '-dFirstPage=1',

                '-dLastPage=1',

                '-sOutputFile='.$outArg,

                $pdfArg,

            ]);

            $process->setTimeout(120);

            $process->run();



            if (! $process->isSuccessful() || ! is_readable($tmp) || (int) filesize($tmp) === 0) {

                @unlink($tmp);

                Log::warning('cnh.pdf.raster_gs_cli_failed', [

                    'exit_code' => $process->getExitCode(),

                    'stderr' => mb_substr($process->getErrorOutput(), 0, 800),

                ]);



                return new CnhRasterizeResult(null, $failConvert);

            }



            return new CnhRasterizeResult($tmp, null);

        } catch (Throwable $e) {

            @unlink($tmp);

            Log::warning('cnh.pdf.raster_gs_cli_exception', ['message' => $e->getMessage()]);



            return new CnhRasterizeResult(null, $failConvert);

        }

    }



    private function resolveGhostscriptExecutable(): ?string

    {

        $raw = trim((string) config('cnh.ghostscript_path', ''));

        if ($raw === '') {

            return null;

        }



        $exe = CnhCliPaths::normalize($raw);



        return is_file($exe) ? $exe : null;

    }



    /**

     * O delegate PDF→PS do ImageMagick invoca o Ghostscript; no Windows o PHP

     * (Apache) muitas vezes não herda o PATH do utilizador. CNH_GHOSTSCRIPT_PATH

     * deve apontar para gswin64c.exe (ou gswin32c.exe).

     */

    private function applyGhostscriptEnvFromConfig(): void

    {

        $exe = $this->resolveGhostscriptExecutable();

        if ($exe === null) {

            return;

        }



        putenv('GS_PROG='.$exe);



        $bin = dirname($exe);

        $sep = PATH_SEPARATOR;

        $path = getenv('PATH');

        if (! is_string($path) || $path === '') {

            putenv('PATH='.$bin);



            return;

        }



        $needle = strtolower(str_replace('\\', '/', $bin));

        foreach (explode($sep, $path) as $part) {

            if ($part === '') {

                continue;

            }

            if (strtolower(str_replace('\\', '/', $part)) === $needle) {

                return;

            }

        }



        putenv('PATH='.$bin.$sep.$path);

    }

}

