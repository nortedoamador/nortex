<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\URL;
use LogicException;

trait HasOpaqueAnexoRoutes
{
    use HasUuids;

    abstract protected function anexoInlineRouteName(): string;

    protected function anexoDownloadRouteName(): ?string
    {
        return null;
    }

    protected function anexoPrintRouteName(): ?string
    {
        return null;
    }

    protected function anexoDestroyRouteName(): ?string
    {
        return null;
    }

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function signedInlineUrl(): string
    {
        return URL::temporarySignedRoute(
            $this->anexoInlineRouteName(),
            $this->signedAnexoUrlExpiration(),
            ['anexo' => $this]
        );
    }

    public function signedDownloadUrl(): string
    {
        $route = $this->anexoDownloadRouteName();

        if ($route === null) {
            throw new LogicException('Download route is not configured for this attachment model.');
        }

        return URL::temporarySignedRoute(
            $route,
            $this->signedAnexoUrlExpiration(),
            ['anexo' => $this]
        );
    }

    public function signedPrintUrl(): string
    {
        $route = $this->anexoPrintRouteName();

        if ($route === null) {
            throw new LogicException('Print route is not configured for this attachment model.');
        }

        return URL::temporarySignedRoute(
            $route,
            $this->signedAnexoUrlExpiration(),
            ['anexo' => $this]
        );
    }

    public function opaqueDestroyUrl(): string
    {
        $route = $this->anexoDestroyRouteName();

        if ($route === null) {
            throw new LogicException('Destroy route is not configured for this attachment model.');
        }

        return route($route, ['anexo' => $this]);
    }

    protected function signedAnexoUrlExpiration(): \DateTimeInterface
    {
        return now()->addMinutes(max(1, (int) config('nortex.anexos.signed_url_ttl_minutes', 60)));
    }
}
