<?php

namespace App\Services\Cnh;

use Throwable;

/**
 * Pré-processamento antes de QR e OCR: grayscale, nitidez, normalize, contraste.
 */
final class CnhImagePreprocessor
{
    /**
     * Gera PNG temporário. Devolve null se Imagick indisponível ou falhar (o chamador usa o original).
     */
    public function preprocess(string $absoluteImagePath): ?string
    {
        if (! extension_loaded('imagick')) {
            return null;
        }

        if (! is_readable($absoluteImagePath)) {
            return null;
        }

        try {
            $im = new \Imagick($absoluteImagePath);

            $white = new \ImagickPixel('white');
            $im->setImageBackgroundColor($white);
            if (defined('Imagick::ALPHACHANNEL_REMOVE')) {
                $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
            }
            $im->setBackgroundColor($white);

            if (defined('Imagick::LAYERMETHOD_FLATTEN')) {
                $flat = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $im->clear();
                $im->destroy();
                $im = $flat;
            }

            $im->setImageType(\Imagick::IMGTYPE_GRAYSCALE);
            $im->sharpenImage(0, 1.2);
            $im->normalizeImage();
            $im->contrastImage(true);
            $im->contrastImage(true);
            $im->sharpenImage(0, 0.6);

            $im->setImageFormat('png');

            $base = tempnam(sys_get_temp_dir(), 'nxpp');
            if ($base === false) {
                $im->clear();
                $im->destroy();

                return null;
            }
            @unlink($base);
            $tmp = $base.'.png';
            $im->writeImage($tmp);
            $im->clear();
            $im->destroy();

            return is_readable($tmp) && filesize($tmp) > 0 ? $tmp : null;
        } catch (Throwable) {
            return null;
        }
    }
}
