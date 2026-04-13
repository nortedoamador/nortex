<?php

namespace Database\Seeders;

use App\Enums\ProcessoStatus;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\PlatformTipoProcesso;
use App\Models\Processo;
use App\Models\User;
use App\Services\EmpresaProcessosDefaultsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DemoProcessosSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->where('slug', 'demo')->first();
        if (! $empresa) {
            return;
        }

        $user = User::query()->where('email', 'admin@nortex.local')->first();
        if ($user) {
            Auth::login($user);
        }

        app(EmpresaProcessosDefaultsService::class)->garantirTemplateBasico($empresa);

        $tipo = PlatformTipoProcesso::query()
            ->where('slug', 'cha-inscricao-arrais-amador')
            ->first();

        if (! $tipo) {
            Auth::logout();

            return;
        }

        $cliente = Cliente::query()->updateOrCreate(
            ['empresa_id' => $empresa->id, 'email' => 'cliente@example.com'],
            [
                'nome' => 'Cliente demonstração',
                'cpf' => '529.982.247-25',
                'tipo_documento' => 'pf',
                'telefone' => '2133334444',
                'rg' => '12.345.678-9',
                'orgao_emissor' => 'DETRAN/RJ',
                'data_emissao_rg' => '2015-03-10',
                'nacionalidade' => 'Brasileira',
                'naturalidade' => 'Rio de Janeiro',
                'cep' => '20040020',
                'endereco' => 'Av. Alm. Barroso',
                'bairro' => 'Centro',
                'cidade' => 'Rio de Janeiro',
                'uf' => 'RJ',
                'numero' => '100',
                'complemento' => null,
                'apartamento' => null,
                'celular' => '21999990000',
            ],
        );

        if (Processo::query()->where('empresa_id', $empresa->id)->doesntExist()) {
            Processo::query()->create([
                'empresa_id' => $empresa->id,
                'cliente_id' => $cliente->id,
                'platform_tipo_processo_id' => $tipo->id,
                'status' => ProcessoStatus::EmMontagem,
            ]);

            Processo::query()->create([
                'empresa_id' => $empresa->id,
                'cliente_id' => $cliente->id,
                'platform_tipo_processo_id' => $tipo->id,
                'status' => ProcessoStatus::Protocolado,
            ]);
        }

        Auth::logout();
    }
}
