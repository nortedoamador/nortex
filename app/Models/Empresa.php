<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Empresa extends Model
{
    /** @use HasFactory<\Database\Factories\EmpresaFactory> */
    use HasFactory;

    protected $fillable = [
        'nome',
        'slug',
        'cnpj',
        'uf',
        'ativo',
        'email_contato',
        'telefone',
        'logo_path',
        'plan_id',
        'plan_overrides',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'plan_overrides' => 'array',
            'documento_modelos_lab_slugs_ocultos' => 'array',
        ];
    }

    public function documentoModeloLabSlugEstaOculto(string $slug): bool
    {
        $slug = Str::lower(trim($slug));
        if ($slug === '') {
            return false;
        }

        return in_array($slug, $this->documento_modelos_lab_slugs_ocultos ?? [], true);
    }

    public function addDocumentoModeloLabSlugOculto(string $slug): void
    {
        $slug = Str::lower(trim($slug));
        if ($slug === '') {
            return;
        }

        $list = $this->documento_modelos_lab_slugs_ocultos ?? [];
        if (in_array($slug, $list, true)) {
            return;
        }

        $list[] = $slug;
        $this->documento_modelos_lab_slugs_ocultos = array_values($list);
        $this->save();
    }

    public function removeDocumentoModeloLabSlugOculto(string $slug): void
    {
        $slug = Str::lower(trim($slug));
        if ($slug === '') {
            return;
        }

        $filtered = array_values(array_filter(
            $this->documento_modelos_lab_slugs_ocultos ?? [],
            static fn (mixed $s): bool => is_string($s) && $s !== $slug,
        ));

        $this->documento_modelos_lab_slugs_ocultos = $filtered;
        $this->save();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }
}

