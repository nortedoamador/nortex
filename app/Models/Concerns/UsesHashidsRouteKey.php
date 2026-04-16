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

        $pair = app(TenantHashids::class)->decodePair((string) $value);
        if ($pair === null || $pair[0] !== static::routeHashidType()) {
            return null;
        }

        return $this->whereKey($pair[1])->first();
    }
}
