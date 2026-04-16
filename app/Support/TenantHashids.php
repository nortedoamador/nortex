<?php

namespace App\Support;

use Hashids\Hashids;

final class TenantHashids
{
    public const TYPE_CLIENTE = 1;

    public const TYPE_EMBARCACAO = 2;

    public const TYPE_HABILITACAO = 3;

    public const TYPE_PROCESSO = 4;

    public const TYPE_AULA_NAUTICA = 5;

    private Hashids $hashids;

    public function __construct()
    {
        $salt = config('hashids.salt');
        if (! is_string($salt) || $salt === '') {
            $salt = hash('sha256', (string) config('app.key'));
        }

        $this->hashids = new Hashids($salt, (int) config('hashids.min_length', 8));
    }

    public function encode(int $type, int $id): string
    {
        return $this->hashids->encode($type, $id);
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    public function decodePair(string $hash): ?array
    {
        if ($hash === '') {
            return null;
        }

        $numbers = $this->hashids->decode($hash);
        if (count($numbers) !== 2) {
            return null;
        }

        return [(int) $numbers[0], (int) $numbers[1]];
    }
}
