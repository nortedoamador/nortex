<?php

namespace App\Support;

use Illuminate\Encryption\Encrypter;
use RuntimeException;

/**
 * Criptografia de ficheiros com chave dedicada (FILE_ENCRYPTION_KEY), independente da APP_KEY.
 */
final class FileEncryption
{
    private static ?Encrypter $encrypter = null;

    public static function encrypter(): Encrypter
    {
        if (self::$encrypter instanceof Encrypter) {
            return self::$encrypter;
        }

        $key = (string) config('app.file_encryption_key', '');
        if ($key === '') {
            throw new RuntimeException('FILE_ENCRYPTION_KEY não está definida no .env.');
        }

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded === false || $decoded === '') {
                throw new RuntimeException('FILE_ENCRYPTION_KEY base64 inválida.');
            }
            $key = $decoded;
        }

        self::$encrypter = new Encrypter($key, (string) config('app.cipher', 'AES-256-CBC'));

        return self::$encrypter;
    }

    public static function encrypt(string $plain): string
    {
        return self::encrypter()->encryptString($plain);
    }

    public static function decrypt(string $payload): string
    {
        return self::encrypter()->decryptString($payload);
    }
}
