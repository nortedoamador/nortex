<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\DocumentoModelo;
use App\Models\DocumentoModeloGlobal;
use App\Models\Empresa;
use App\Models\User;
use App\Services\DocumentoModeloGlobalPropagationService;
use App\Services\EmpresaProcessosDefaultsService;
use App\Support\DocumentoModeloSincroniaDiscoBd;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class DocumentoModeloGlobalTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_propagacao_nao_sobrescreve_personalizado(): void
    {
        $empresa = Empresa::factory()->create();
        $global = DocumentoModeloGlobal::query()->where('slug', 'anexo-2g')->first();
        $this->assertNotNull($global);

        $svc = app(EmpresaProcessosDefaultsService::class);
        $svc->garantirModeloGlobalNaEmpresa($empresa, $global);

        $modelo = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'anexo-2g')
            ->first();
        $this->assertNotNull($modelo);

        $modelo->update([
            'conteudo' => '<blade-personalizado />',
            'conteudo_upload_bruto' => '<blade-personalizado />',
            'personalizado' => true,
            'global_synced_at' => null,
        ]);

        $global->update(['conteudo' => '<blade-global-novo />']);

        $prop = app(DocumentoModeloGlobalPropagationService::class);
        $prop->propagarParaEmpresasNaoPersonalizadas($global->fresh());

        $modelo->refresh();
        $this->assertSame('<blade-personalizado />', $modelo->conteudo);
        $this->assertTrue($modelo->personalizado);
    }

    public function test_propagacao_actualiza_nao_personalizado(): void
    {
        $empresa = Empresa::factory()->create();
        $global = DocumentoModeloGlobal::query()->where('slug', 'anexo-2g')->first();
        $this->assertNotNull($global);

        $svc = app(EmpresaProcessosDefaultsService::class);
        $svc->garantirModeloGlobalNaEmpresa($empresa, $global);

        $global->update(['conteudo' => '<p>propagado</p>']);

        app(DocumentoModeloGlobalPropagationService::class)->propagarParaEmpresasNaoPersonalizadas($global->fresh());

        $modelo = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'anexo-2g')
            ->first();

        $this->assertNotNull($modelo);
        $this->assertSame('<p>propagado</p>', $modelo->conteudo);
        $this->assertFalse($modelo->personalizado);
    }

    public function test_sincronia_disco_nao_aplica_com_vinculo_global(): void
    {
        $empresa = Empresa::factory()->create();
        $global = DocumentoModeloGlobal::query()->where('slug', 'anexo-2g')->first();
        $this->assertNotNull($global);

        app(EmpresaProcessosDefaultsService::class)->garantirModeloGlobalNaEmpresa($empresa, $global);

        $modelo = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'anexo-2g')
            ->firstOrFail();

        $modelo->update(['conteudo' => '<p>bd</p>', 'conteudo_upload_bruto' => '<p>bd</p>']);

        $this->assertFalse(DocumentoModeloSincroniaDiscoBd::aplicar($modelo->fresh()));

        $modelo->refresh();
        $this->assertSame('<p>bd</p>', $modelo->conteudo);
    }

    public function test_propagacao_apenas_empresas_selecionadas(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $global = DocumentoModeloGlobal::query()->where('slug', 'anexo-2g')->firstOrFail();

        $svc = app(EmpresaProcessosDefaultsService::class);
        $svc->garantirModeloGlobalNaEmpresa($empresaA, $global);
        $svc->garantirModeloGlobalNaEmpresa($empresaB, $global);

        $global->update(['conteudo' => '<p>só-empresa-a</p>']);

        app(DocumentoModeloGlobalPropagationService::class)
            ->propagarParaEmpresasNaoPersonalizadas($global->fresh(), [$empresaA->id]);

        $modeloA = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaA->id)
            ->where('slug', 'anexo-2g')
            ->firstOrFail();

        $modeloB = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaB->id)
            ->where('slug', 'anexo-2g')
            ->firstOrFail();

        $this->assertSame('<p>só-empresa-a</p>', $modeloA->conteudo);
        $this->assertNotSame('<p>só-empresa-a</p>', $modeloB->conteudo);
    }

    public function test_repor_esqueleto_global_na_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $global = DocumentoModeloGlobal::query()->where('slug', 'anexo-2g')->firstOrFail();

        app(EmpresaProcessosDefaultsService::class)->garantirModeloGlobalNaEmpresa($empresa, $global);

        $modelo = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'anexo-2g')
            ->firstOrFail();

        $modelo->update([
            'conteudo' => '<p>local</p>',
            'conteudo_upload_bruto' => '<p>local</p>',
            'personalizado' => true,
        ]);

        $global->update(['conteudo' => '<p>global-reset</p>']);

        $err = app(EmpresaProcessosDefaultsService::class)->reporEsqueletoGlobalNaEmpresa($empresa, 'anexo-2g');
        $this->assertNull($err);

        $modelo->refresh();
        $this->assertSame('<p>global-reset</p>', $modelo->conteudo);
        $this->assertFalse($modelo->personalizado);
    }

    public function test_platform_admin_acessa_laboratorio_documentos_globais(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get(route('platform.cadastros.documentos-automatizados.laboratorio'))
            ->assertOk();
    }

    public function test_platform_preview_documento_global_com_cliente(): void
    {
        $empresa = Empresa::factory()->create();
        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente Preview Global',
            'cpf' => '52998224725',
        ]);
        $global = DocumentoModeloGlobal::query()->where('slug', 'anexo-2g')->firstOrFail();
        $global->update(['conteudo' => '<p>preview-global</p>']);

        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get(route('platform.cadastros.documentos-automatizados.preview', [
                'empresa_id' => $empresa->id,
                'cliente_id' => $cliente->id,
                'slug' => 'anexo-2g',
                'format' => 'html',
            ]))
            ->assertOk()
            ->assertSee('preview-global', false);
    }

    public function test_platform_elimina_global_com_vinculos_descola_copias_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $global = DocumentoModeloGlobal::query()->create([
            'slug' => 'teste-delete-global-descola',
            'titulo' => 'Teste descola',
            'referencia' => null,
            'conteudo' => '<p>tmp</p>',
        ]);
        app(EmpresaProcessosDefaultsService::class)->garantirModeloGlobalNaEmpresa($empresa, $global);

        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->from(route('platform.cadastros.documentos-automatizados.edit', $global))
            ->delete(route('platform.cadastros.documentos-automatizados.destroy', $global))
            ->assertRedirect(route('platform.cadastros.documentos-automatizados.index'));

        $this->assertNull(DocumentoModeloGlobal::query()->where('slug', 'teste-delete-global-descola')->first());

        $modelo = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'teste-delete-global-descola')
            ->first();
        $this->assertNotNull($modelo);
        $this->assertNull($modelo->documento_modelo_global_id);
    }

    public function test_platform_elimina_global_e_apaga_copias_empresa_quando_solicitado(): void
    {
        $empresa = Empresa::factory()->create();
        $global = DocumentoModeloGlobal::query()->create([
            'slug' => 'teste-delete-global-copias',
            'titulo' => 'Teste cópias',
            'referencia' => null,
            'conteudo' => '<p>tmp</p>',
        ]);
        app(EmpresaProcessosDefaultsService::class)->garantirModeloGlobalNaEmpresa($empresa, $global);

        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->from(route('platform.cadastros.documentos-automatizados.edit', $global))
            ->delete(route('platform.cadastros.documentos-automatizados.destroy', $global), [
                'apagar_copias_empresa' => '1',
            ])
            ->assertRedirect(route('platform.cadastros.documentos-automatizados.index'));

        $this->assertNull(DocumentoModeloGlobal::query()->where('slug', 'teste-delete-global-copias')->first());
        $this->assertNull(
            DocumentoModelo::query()
                ->withoutGlobalScope('empresa')
                ->where('empresa_id', $empresa->id)
                ->where('slug', 'teste-delete-global-copias')
                ->first()
        );
    }
}
