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

        $priceFull = config('services.stripe.price_full');
        if (is_string($priceFull) && $priceFull !== '') {
            $empresa->forceFill([
                'pagamento_inicial_pendente' => false,
                'stripe_customer_id' => 'cus_nortex_local',
                'stripe_subscription_id' => 'sub_nortex_local',
                'stripe_subscription_status' => 'active',
                'stripe_current_price_id' => $priceFull,
                'stripe_subscription_cancel_at_period_end' => false,
            ])->save();
        }

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
