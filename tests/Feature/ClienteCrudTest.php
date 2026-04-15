<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Support\FileEncryption;
use App\Support\ClienteTiposAnexo;
use Tests\Concerns\SafeRefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClienteCrudTest extends TestCase
{
    use SafeRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payloadFichaCliente(array $overrides = []): array
    {
        return array_merge([
            'tipo_documento' => 'pf',
            'nome' => 'Maria Silva',
            'cpf' => '52998224725',
            'documento_identidade_numero' => '1234567',
            'documento_identidade_tipo' => 'rg',
            'orgao_emissor' => 'SSP/RJ',
            'data_emissao_rg' => '2010-05-15',
            'nacionalidade' => 'Brasileira',
            'naturalidade' => 'Rio de Janeiro',
            'cep' => '20000000',
            'endereco' => 'Rua A',
            'bairro' => 'Centro',
            'cidade' => 'Rio de Janeiro',
            'uf' => 'RJ',
            'numero' => '100',
            'complemento' => '',
            'apartamento' => '',
            'telefone' => '2133334444',
            'celular' => '',
            'email' => 'maria@example.com',
        ], $overrides);
    }

    public function test_usuario_sem_clientes_manage_nao_acessa_criar(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $instrutor = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'instrutor')
            ->firstOrFail();
        $user->roles()->sync([$instrutor->id]);

        $this->actingAs($user)
            ->get(route('clientes.create'))
            ->assertForbidden();
    }

    public function test_administrador_cadastra_edita_e_remove_cliente(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin)
            ->post(route('clientes.store'), $this->payloadFichaCliente())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('clientes.index'));

        $cliente = Cliente::query()->where('email', 'maria@example.com')->firstOrFail();
        $this->assertSame('Maria Silva', $cliente->nome);
        $this->assertSame('pf', $cliente->tipo_documento);

        $this->actingAs($admin)
            ->patch(route('clientes.update', $cliente), $this->payloadFichaCliente([
                'nome' => 'Maria S.',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('clientes.show', $cliente));

        $this->assertSame('Maria S.', $cliente->fresh()->nome);

        $this->actingAs($admin)
            ->delete(route('clientes.destroy', $cliente))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('clientes.index'));

        $this->assertNull($cliente->fresh());
    }

    public function test_busca_na_listagem_filtra_por_nome(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Alpha Ltda',
            'cpf' => null,
            'email' => 'a@example.com',
        ]);
        Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Beta SA',
            'cpf' => null,
            'email' => 'b@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('clientes.index', ['q' => 'Beta']))
            ->assertOk()
            ->assertSee('Beta SA', false)
            ->assertDontSee('Alpha Ltda', false);
    }

    public function test_cpf_duplicado_na_mesma_empresa_e_rejeitado(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Primeiro',
            'cpf' => '529.982.247-25',
            'tipo_documento' => 'pf',
            'email' => 'primeiro@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('clientes.store'), $this->payloadFichaCliente([
                'nome' => 'Segundo',
                'cpf' => '52998224725',
                'email' => 'segundo@example.com',
            ]))
            ->assertSessionHasErrors('cpf');
    }

    public function test_cadastro_com_cnpj_valido(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $payload = $this->payloadFichaCliente([
            'tipo_documento' => 'pj',
            'nome' => 'Empresa LTDA',
            'cpf' => '11222333000181',
            'email' => 'contato@empresa.test',
        ]);
        unset($payload['nacionalidade'], $payload['naturalidade']);

        $this->actingAs($admin)
            ->post(route('clientes.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('clientes.index'));

        $cliente = Cliente::query()->where('email', 'contato@empresa.test')->firstOrFail();
        $this->assertSame('pj', $cliente->tipo_documento);
        $this->assertStringContainsString('11.222.333', $cliente->cpf ?? '');
    }

    public function test_cadastro_pj_com_anexo_no_primeiro_slot_grava_contrato_social(): void
    {
        Storage::fake('s3');

        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $file = UploadedFile::fake()->create('contrato.pdf', 120, 'application/pdf');

        $payload = $this->payloadFichaCliente([
            'tipo_documento' => 'pj',
            'nome' => 'Empresa Com Anexo SA',
            'cpf' => '11222333000181',
            'email' => 'pj.anexo@test.com',
        ]);
        unset($payload['nacionalidade'], $payload['naturalidade']);

        $this->actingAs($admin)
            ->post(route('clientes.store'), array_merge($payload, [
                'anexo_cnh' => [$file],
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('clientes.index'));

        $cliente = Cliente::query()->where('email', 'pj.anexo@test.com')->firstOrFail();
        $this->assertCount(1, $cliente->anexos);
        $this->assertSame(ClienteTiposAnexo::CONTRATO_SOCIAL, $cliente->anexos->first()->tipo_codigo);
    }

    public function test_cadastro_ficha_com_anexo_cnh(): void
    {
        Storage::fake('s3');

        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $file = UploadedFile::fake()->create('cnh.pdf', 120, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('clientes.store'), array_merge($this->payloadFichaCliente([
                'email' => 'comcnh@test.com',
            ]), [
                'anexo_cnh' => [$file],
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('clientes.index'));

        $cliente = Cliente::query()->where('email', 'comcnh@test.com')->firstOrFail();
        $this->assertCount(1, $cliente->anexos);
        $this->assertSame(ClienteTiposAnexo::CNH, $cliente->anexos->first()->tipo_codigo);
        Storage::disk('s3')->assertExists($cliente->anexos->first()->path);
    }

    public function test_download_de_cliente_retorna_payload_descriptografado(): void
    {
        Storage::fake('s3');

        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $plain = 'cliente-anexo-conteudo-original';
        $file = UploadedFile::fake()->createWithContent('cnh.pdf', $plain);

        $this->actingAs($admin)
            ->post(route('clientes.store'), array_merge($this->payloadFichaCliente([
                'email' => 'download@test.com',
            ]), [
                'anexo_cnh' => [$file],
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('clientes.index'));

        $cliente = Cliente::query()->where('email', 'download@test.com')->firstOrFail();
        $anexo = $cliente->anexos()->firstOrFail();
        $raw = Storage::disk('s3')->get($anexo->path);

        $this->assertNotSame($plain, $raw);
        $this->assertSame($plain, FileEncryption::decrypt($raw));

        $response = $this->actingAs($admin)
            ->get($anexo->signedDownloadUrl());

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));
        $this->assertSame($plain, $response->streamedContent());

        $this->actingAs($admin)
            ->get(route('clientes.anexos.download', ['anexo' => $anexo]))
            ->assertForbidden();
    }
}
