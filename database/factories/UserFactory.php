<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaRbacService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->empresa_id) {
                return;
            }

            $empresa = Empresa::query()->find($user->empresa_id);
            if (! $empresa) {
                return;
            }

            $rbac = app(EmpresaRbacService::class);
            $rbac->bootstrapEmpresa($empresa);

            if ($user->roles()->doesntExist()) {
                $rbac->assignRole($user, 'administrador');
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** Administração global da plataforma (sem tenant obrigatório). */
    public function platformAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => null,
            'is_platform_admin' => true,
        ]);
    }
}
