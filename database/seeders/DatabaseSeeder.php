<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaRbacService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionsSeeder::class);

        $empresa = Empresa::query()->firstOrCreate(
            ['slug' => 'demo'],
            [
                'nome' => 'Empresa Demo',
                'cnpj' => null,
                'ativo' => true,
            ],
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'admin@nortex.local'],
            [
                'empresa_id' => $empresa->id,
                'name' => 'Admin Demo',
                'password' => 'password',
            ],
        );

        if ((int) $user->empresa_id !== (int) $empresa->id) {
            $user->forceFill(['empresa_id' => $empresa->id])->save();
        }

        $rbac = app(EmpresaRbacService::class);
        $rbac->bootstrapEmpresa($empresa);

        if ($user->roles()->where('slug', 'administrador')->doesntExist()) {
            $rbac->assignRole($user, 'administrador');
        }

        $this->call(DocumentoModelosSeeder::class);
        $this->call(DemoProcessosSeeder::class);
        $this->call(DemoClientesEmbarcacoesSeeder::class);
    }
}
