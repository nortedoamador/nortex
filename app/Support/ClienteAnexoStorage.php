<?php

namespace App\Support;

use App\Models\ClienteAnexo;
use Illuminate\Http\UploadedFile;

/** Facade fina para anexos de cliente (usa {@see EncryptedS3AnexoStorage}). */
final class ClienteAnexoStorage
{
    public const DISK = EncryptedS3AnexoStorage::DISK;

    public static function storeEncryptedUpload(UploadedFile $file, string $dir): string
    {
        return EncryptedS3AnexoStorage::storeEncryptedUpload($file, $dir);
    }

    public static function readPlainContents(ClienteAnexo $anexo): string
    {
        return EncryptedS3AnexoStorage::readPlain($anexo->disk, $anexo->path);
    }

    public static function exists(ClienteAnexo $anexo): bool
    {
        return EncryptedS3AnexoStorage::exists($anexo->disk, $anexo->path);
    }
}
