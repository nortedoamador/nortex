<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Armazenamento de anexos no disco s3 (Backblaze B2) com payload cifrado (FILE_ENCRYPTION_KEY).
 */
final class EncryptedS3AnexoStorage
{
    public const DISK = 's3';

    public static function storeEncryptedUpload(UploadedFile $file, string $dir): string
    {
        $path = trim($dir, '/').'/'.Str::uuid()->toString().'.enc';
        $contents = $file->get();
        if ($contents === false) {
            throw new \RuntimeException('Falha ao ler o arquivo enviado para criptografia.');
        }

        Storage::disk(self::DISK)->put($path, FileEncryption::encrypt($contents));

        return $path;
    }

    /** Grava conteúdo já em claro (ex.: cópia a partir da ficha do cliente) cifrado no disco. */
    public static function storeEncryptedPlainContents(string $dir, string $plainContents): string
    {
        $path = trim($dir, '/').'/'.Str::uuid()->toString().'.enc';
        Storage::disk(self::DISK)->put($path, FileEncryption::encrypt($plainContents));

        return $path;
    }

    public static function isEncryptedDisk(string $disk): bool
    {
        return $disk === self::DISK;
    }

    public static function readPlain(string $disk, string $path): string
    {
        $raw = Storage::disk($disk)->get($path);

        return self::isEncryptedDisk($disk) ? FileEncryption::decrypt($raw) : $raw;
    }

    public static function exists(string $disk, string $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }
}
