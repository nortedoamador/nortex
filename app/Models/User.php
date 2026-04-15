<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var array<string, true>|null Slugs de permissão efetivos (cache por instância durante o pedido). */
    private ?array $permissionSlugsCache = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'empresa_id',
        'is_platform_admin',
        'is_disabled',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
            'is_disabled' => 'boolean',
        ];
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasPermission(string $slug): bool
    {
        if (! isset($this->resolvedPermissionSlugs()[$slug])) {
            return false;
        }

        if ($this->empresa_id && str_starts_with($slug, 'financeiro.')) {
            $empresa = $this->relationLoaded('empresa') ? $this->empresa : $this->empresa()->first();
            if ($empresa && ! $empresa->billingIncludesFinanceiro()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $slugs
     */
    public function hasAnyPermission(array $slugs): bool
    {
        foreach ($slugs as $slug) {
            if ($this->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, true>
     */
    private function resolvedPermissionSlugs(): array
    {
        if ($this->permissionSlugsCache !== null) {
            return $this->permissionSlugsCache;
        }

        if (! $this->empresa_id) {
            return $this->permissionSlugsCache = [];
        }

        $roles = $this->roles()
            ->where('roles.empresa_id', $this->empresa_id)
            ->with('permissions:id,slug')
            ->get();

        $set = [];
        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                $set[$permission->slug] = true;
            }
        }

        return $this->permissionSlugsCache = $set;
    }
}
