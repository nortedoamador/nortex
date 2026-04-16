<?php

namespace App\Models\Concerns;

use App\Support\TenantHashids;
use Illuminate\Database\Eloquent\Model;

/**
 * URLs públicas usam Hashids (tipo + id) para não expor o id sequencial.
 *
 * @mixin Model
 */
trait UsesHashidsRouteKey
{
    abstract public static function routeHashidType(): int;

    public function getRouteKey(): string
    {
        $id = $this->getAttribute($this->getRouteKeyName());
        if ($id === null) {
            return '';
        }

        return app(TenantHashids::class)->encode(static::routeHashidType(), (int) $id);
    }

    /**
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        $raw = (string) $value;
        $pair = app(TenantHashids::class)->decodePair($raw);
        if ($pair !== null && $pair[0] === static::routeHashidType()) {
            return $this->whereKey($pair[1])->first();
        }

        // Compatibilidade: URLs antigas ou integrações que ainda usam o id numérico.
        if ($raw !== '' && ctype_digit($raw)) {
            return $this->whereKey((int) $raw)->first();
        }

        return null;
    }
}
