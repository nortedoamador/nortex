<?php

namespace App\Support;

/**
 * Redimensiona e recompressa imagens raster (JPEG/PNG/WebP) quando a extensão GD está disponível.
 */
final class UploadRasterCompressor
{
    /**
     * @return array{0: string, 1: string}|null [binary, mime] ou null se não aplicável / falhou
     */
    public static function tryCompress(string $binary, string $mime): ?array
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $mime = strtolower(trim($mime));
        if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $src = @imagecreatefromstring($binary);
        if ($src === false) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        if ($w < 1 || $h < 1) {
            imagedestroy($src);

            return null;
        }

        $maxEdge = 2400;
        $scale = 1.0;
        if ($w > $maxEdge || $h > $maxEdge) {
            $scale = min($maxEdge / $w, $maxEdge / $h);
        }
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $dst = imagecreatetruecolor($nw, $nh);
        if ($dst === false) {
            imagedestroy($src);

            return null;
        }

        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);

        if (! imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h)) {
            imagedestroy($src);
            imagedestroy($dst);

            return null;
        }
        imagedestroy($src);

        $outMime = $mime === 'image/png' ? 'image/png' : 'image/jpeg';
        ob_start();
        if ($outMime === 'image/png') {
            imagepng($dst, null, 6);
        } else {
            imagejpeg($dst, null, 82);
        }
        $compressed = (string) ob_get_clean();
        imagedestroy($dst);

        if ($compressed === '' || strlen($compressed) >= strlen($binary)) {
            return null;
        }

        return [$compressed, $outMime];
    }
}
