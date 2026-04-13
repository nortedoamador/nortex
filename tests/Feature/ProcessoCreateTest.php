<?php

namespace Tests\Feature;

use App\Enums\ProcessoStatus;
use App\Enums\TipoProcessoCategoria;
use App\Models\Cliente;
use App\Models\DocumentoTipo;
use App\Models\Embarcacao;
use App\Models\Empresa;
use App\Models\Habilitacao;
use App\Models\PlatformTipoProcesso;
use App\Models\Processo;
use App\Models\TipoProcesso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\EmpresaProcessosDefaultsService;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;
use App\Models\ProcessoDocumento;
use App\Enums\ProcessoDocumentoStatus;
use App\Support\Normam211DocumentoCodigos;

class ProcessoCreateTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_usuario_sem_permissao_nao_acessa_formulario(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $user->roles()->detach();

        $this->actingAs($user)
            ->get(route('processos.create'))
            ->assertForbidden();
    }

    public function test_criar_processo_redireciona_para_detalhe_e_gera_checklist(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $tipo = PlatformTipoProcesso::query()->create([
            'nome' => 'Tipo teste',
            'slug' => 'tipo-teste',
            'categoria' => TipoProcessoCategoria::Embarcacao->value,
            'ativo' => true,
            'ordem' => 0,
        ]);
        $tipoTenant = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Tipo teste',
            'slug' => 'tipo-teste',
            'categoria' => TipoProcessoCategoria::Embarcacao,
        ]);

        $doc = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'DOCX',
            'nome' => 'Documento X',
        ]);
        DB::table('documento_processo')->insert([
            'empresa_id' => $empresa->id,
            'tipo_processo_id' => $tipoTenant->id,
            'platform_tipo_processo_id' => $tipo->id,
            'documento_tipo_id' => $doc->id,
            'obrigatorio' => true,
            'ordem' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente A',
            'cpf' => '52998224725',
        ]);
        $emb = Embarcacao::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Barco A',
        ]);

        $this->actingAs($user);

        $this->get(route('processos.create'))->assertOk();

        $response = $this->post(route('processos.store'), [
            'platform_tipo_processo_id' => $tipo->id,
            'cliente_id' => $cliente->id,
            'embarcacao_id' => $emb->id,
            'cpf' => '52998224725',
            'nome_interessado' => 'Cliente A',
            'jurisdicao' => Habilitacao::JURISDICOES[0],
        ]);

        $response->assertSessionHasNoErrors();
        $processo = Processo::query()->where('platform_tipo_processo_id', $tipo->id)->where('cliente_id', $cliente->id)->firstOrFail();
        $response->assertRedirect(route('processos.show', $processo));

        $this->assertSame(ProcessoStatus::EmMontagem, $processo->status);
        $this->assertSame(Habilitacao::JURISDICOES[0], $processo->jurisdicao);
        $this->assertCount(1, $processo->documentosChecklist);
    }

    public function test_store_com_accept_json_retorna_documentos_do_checklist(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $tipo = PlatformTipoProcesso::query()->create([
            'nome' => 'Tipo JSON',
            'slug' => 'tipo-json',
            'categoria' => TipoProcessoCategoria::Embarcacao->value,
            'ativo' => true,
            'ordem' => 0,
        ]);
        $tipoTenant = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Tipo JSON',
            'slug' => 'tipo-json',
            'categoria' => TipoProcessoCategoria::Embarcacao,
        ]);

        $doc = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'DOCJ',
            'nome' => 'Doc JSON',
        ]);
        DB::table('documento_processo')->insert([
            'empresa_id' => $empresa->id,
            'tipo_processo_id' => $tipoTenant->id,
            'platform_tipo_processo_id' => $tipo->id,
            'documento_tipo_id' => $doc->id,
            'obrigatorio' => false,
            'ordem' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente J',
            'cpf' => '52998224725',
        ]);
        $emb = Embarcacao::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Barco J',
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('processos.store'), [
            'platform_tipo_processo_id' => $tipo->id,
            'cliente_id' => $cliente->id,
            'embarcacao_id' => $emb->id,
            'cpf' => '52998224725',
            'nome_interessado' => 'Cliente J',
            'jurisdicao' => Habilitacao::JURISDICOES[0],
        ]);

        $response->assertOk();
        $response->assertJsonPath('processo.id', fn ($id) => is_int($id) && $id > 0);
        $response->assertJsonCount(1, 'documentos');
        $response->assertJsonPath('documentos.0.nome', 'Doc JSON');
        $response->assertJsonPath('documentos.0.status', 'pendente');
        $response->assertJsonPath('documentos.0.obrigatorio', false);
    }

    public function test_progresso_considera_todos_os_itens_do_checklist(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $tipo = PlatformTipoProcesso::query()->create([
            'nome' => 'Tipo progresso',
            'slug' => 'tipo-progresso',
            'categoria' => TipoProcessoCategoria::Embarcacao->value,
            'ativo' => true,
            'ordem' => 0,
        ]);
        $tipoTenant = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Tipo progresso',
            'slug' => 'tipo-progresso',
            'categoria' => TipoProcessoCategoria::Embarcacao,
        ]);

        $docReq = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'DREQ',
            'nome' => 'Obrigatório',
        ]);
        $docOpt = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'DOPT',
            'nome' => 'Opcional',
        ]);

        DB::table('documento_processo')->insert([
            [
                'empresa_id' => $empresa->id,
                'tipo_processo_id' => $tipoTenant->id,
                'platform_tipo_processo_id' => $tipo->id,
                'documento_tipo_id' => $docReq->id,
                'obrigatorio' => true,
                'ordem' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => $empresa->id,
                'tipo_processo_id' => $tipoTenant->id,
                'platform_tipo_processo_id' => $tipo->id,
                'documento_tipo_id' => $docOpt->id,
                'obrigatorio' => false,
                'ordem' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente P',
            'cpf' => '52998224725',
        ]);
        $emb = Embarcacao::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Barco P',
        ]);

        $this->actingAs($user);

        $res = $this->postJson(route('processos.store'), [
            'platform_tipo_processo_id' => $tipo->id,
            'cliente_id' => $cliente->id,
            'embarcacao_id' => $emb->id,
            'cpf' => '52998224725',
            'nome_interessado' => 'Cliente P',
            'jurisdicao' => Habilitacao::JURISDICOES[0],
        ]);

        $res->assertOk();

        $processoId = (int) $res->json('processo.id');
        $processo = Processo::query()->findOrFail($processoId);

        $this->assertCount(2, $processo->documentosChecklist);

        $linhaOpt = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->where('documento_tipo_id', $docOpt->id)
            ->firstOrFail();
        $linhaOpt->update(['status' => ProcessoDocumentoStatus::Fisico]);

        $res2 = $this->get(route('processos.show', $processo));
        $res2->assertOk();
        $res2->assertSee('1 / 2 (50', false);
    }

    public function test_lista_processos_index_responde_ok(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('processos.index'))
            ->assertOk();
    }

    public function test_bsade_2b_tem_modelo_e_usa_contexto_embarcacao_do_processo(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        app(EmpresaProcessosDefaultsService::class)->garantirTemplateBasico($empresa);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente BSADE',
            'cpf' => '52998224725',
        ]);
        $emb = Embarcacao::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Barco BSADE',
        ]);

        $tipo = PlatformTipoProcesso::query()
            ->where('slug', 'tie-inscricao-embarcacao-ate-12m')
            ->firstOrFail();

        $this->actingAs($user);

        $res = $this->postJson(route('processos.store'), [
            'platform_tipo_processo_id' => $tipo->id,
            'cliente_id' => $cliente->id,
            'embarcacao_id' => $emb->id,
            'cpf' => '52998224725',
            'nome_interessado' => 'Cliente BSADE',
            'jurisdicao' => Habilitacao::JURISDICOES[0],
        ]);

        $res->assertOk();
        $docs = $res->json('documentos');
        $this->assertIsArray($docs);

        $row = collect($docs)->firstWhere('codigo', 'TIE_BSADE_211_2B_DUAS_VIAS');
        $this->assertIsArray($row);
        $this->assertStringContainsString('Anexo 2-B', (string) ($row['nome'] ?? ''));
        $this->assertNotEmpty($row['url_abrir_modelo'] ?? null);
        $this->assertStringContainsString('contexto_id='.$emb->id, (string) ($row['url_abrir_modelo'] ?? ''));
    }

    public function test_bdmoto_212_2b_tem_modelo_e_usa_contexto_embarcacao_do_processo(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        app(EmpresaProcessosDefaultsService::class)->garantirTemplateBasico($empresa);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente BDMOTO',
            'cpf' => '39053344705',
        ]);
        $emb = Embarcacao::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Jet BDMOTO',
            'tipo' => 'Moto-Aquática/similar',
        ]);

        $tipo = PlatformTipoProcesso::query()
            ->where('slug', 'tie-inscricao-moto-aquatica')
            ->firstOrFail();

        $this->actingAs($user);

        $res = $this->postJson(route('processos.store'), [
            'platform_tipo_processo_id' => $tipo->id,
            'cliente_id' => $cliente->id,
            'embarcacao_id' => $emb->id,
            'cpf' => '39053344705',
            'nome_interessado' => 'Cliente BDMOTO',
            'jurisdicao' => Habilitacao::JURISDICOES[0],
        ]);

        $res->assertOk();
        $docs = $res->json('documentos');
        $this->assertIsArray($docs);

        $row = collect($docs)->firstWhere('codigo', 'TIE_BDMOTO_212_2B');
        $this->assertIsArray($row);
        $this->assertStringContainsString('BDMOTO', (string) ($row['nome'] ?? ''));
        $this->assertSame('anexo-2b-bdmoto-normam212', (string) ($row['modelo_slug'] ?? ''));
        $this->assertNotEmpty($row['url_abrir_modelo'] ?? null);
        $this->assertStringContainsString('contexto_id='.$emb->id, (string) ($row['url_abrir_modelo'] ?? ''));
    }

    public function test_cha_renovacao_exige_habilitacao_selecionada(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        app(EmpresaProcessosDefaultsService::class)->garantirTemplateBasico($empresa);

        $tipo = PlatformTipoProcesso::query()->where('slug', 'cha-renovacao')->firstOrFail();

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente CHA Ren',
            'cpf' => '52998224725',
        ]);

        $this->actingAs($user);

        $res = $this->postJson(route('processos.store'), [
            'platform_tipo_processo_id' => $tipo->id,
            'cliente_id' => $cliente->id,
            'cpf' => '52998224725',
            'nome_interessado' => 'Cliente CHA Ren',
            'jurisdicao' => Habilitacao::JURISDICOES[0],
        ]);

        $res->assertUnprocessable();
        $res->assertJsonValidationErrors('habilitacao_id');
    }

    public function test_cha_extravio_motonauta_dispensa_declaracao_5d_mantem_3d_pendente(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        app(EmpresaProcessosDefaultsService::class)->garantirTemplateBasico($empresa);

        $tipo = PlatformTipoProcesso::query()->where('slug', 'cha-extravio-roubo-furto-dano')->firstOrFail();

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente Extravio MTA',
            'cpf' => '52998224725',
        ]);

        $hab = Habilitacao::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Cliente Extravio MTA',
            'cpf' => '52998224725',
            'categoria' => 'Motonauta',
            'numero_cha' => 'CHA-MTA-01',
        ]);

        $this->actingAs($user);

        $res = $this->postJson(route('processos.store'), [
            'platform_tipo_processo_id' => $tipo->id,
            'cliente_id' => $cliente->id,
            'habilitacao_id' => $hab->id,
            'cpf' => '52998224725',
            'nome_interessado' => 'Cliente Extravio MTA',
            'jurisdicao' => Habilitacao::JURISDICOES[0],
        ]);

        $res->assertOk();

        $processoId = (int) $res->json('processo.id');
        $processo = Processo::query()->findOrFail($processoId);

        $linha5d = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->whereHas('documentoTipo', fn ($q) => $q->where('codigo', 'CHA_DECL_EXTRAVIO_DANO_ANEXO_5D'))
            ->firstOrFail();

        $linha3d = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->whereHas('documentoTipo', fn ($q) => $q->where('codigo', Normam211DocumentoCodigos::CHA_DECL_EXTRAVIO_MTA_3D_212))
            ->firstOrFail();

        $this->assertSame(ProcessoDocumentoStatus::Dispensado, $linha5d->status);
        $this->assertSame(ProcessoDocumentoStatus::Pendente, $linha3d->status);
    }

    public function test_cha_extravio_categoria_mista_dispensa_3d_mantem_5d_pendente(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        app(EmpresaProcessosDefaultsService::class)->garantirTemplateBasico($empresa);

        $tipo = PlatformTipoProcesso::query()->where('slug', 'cha-extravio-roubo-furto-dano')->firstOrFail();

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente Extravio Misto',
            'cpf' => '39053344705',
        ]);

        $hab = Habilitacao::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'nome' => 'Cliente Extravio Misto',
            'cpf' => '39053344705',
            'categoria' => 'Arrais-Amador e Motonauta',
            'numero_cha' => 'CHA-MIX-01',
        ]);

        $this->actingAs($user);

        $res = $this->postJson(route('processos.store'), [
            'platform_tipo_processo_id' => $tipo->id,
            'cliente_id' => $cliente->id,
            'habilitacao_id' => $hab->id,
            'cpf' => '39053344705',
            'nome_interessado' => 'Cliente Extravio Misto',
            'jurisdicao' => Habilitacao::JURISDICOES[0],
        ]);

        $res->assertOk();

        $processoId = (int) $res->json('processo.id');
        $processo = Processo::query()->findOrFail($processoId);

        $linha5d = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->whereHas('documentoTipo', fn ($q) => $q->where('codigo', 'CHA_DECL_EXTRAVIO_DANO_ANEXO_5D'))
            ->firstOrFail();

        $linha3d = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->whereHas('documentoTipo', fn ($q) => $q->where('codigo', Normam211DocumentoCodigos::CHA_DECL_EXTRAVIO_MTA_3D_212))
            ->firstOrFail();

        $this->assertSame(ProcessoDocumentoStatus::Pendente, $linha5d->status);
        $this->assertSame(ProcessoDocumentoStatus::Dispensado, $linha3d->status);
    }
}
