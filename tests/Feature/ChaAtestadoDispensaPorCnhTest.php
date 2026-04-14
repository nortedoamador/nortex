<?php

namespace Tests\Feature;

use App\Enums\ProcessoDocumentoStatus;
use App\Enums\ProcessoStatus;
use App\Enums\TipoProcessoCategoria;
use App\Models\Cliente;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;
use App\Models\User;
use App\Support\ChaChecklistDocumentoCodigos;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class ChaAtestadoDispensaPorCnhTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_cnh_valida_com_anexo_dispensa_atestado_automaticamente(): void
    {
        Storage::fake('s3');

        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $this->actingAs($user);

        $tipo = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'CHA teste dispensa',
            'slug' => 'cha-teste-dispensa',
            'categoria' => TipoProcessoCategoria::Cha,
        ]);
        $platformTipo = PlatformTipoProcesso::query()->create([
            'nome' => $tipo->nome,
            'slug' => $tipo->slug,
            'categoria' => TipoProcessoCategoria::Cha->value,
            'ativo' => true,
            'ordem' => 0,
        ]);

        $cnhTipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => ChaChecklistDocumentoCodigos::CNH_COM_VALIDADE,
            'nome' => 'CNH',
        ]);
        $atestadoTipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => ChaChecklistDocumentoCodigos::ATESTADO_MEDICO_PSICOFISICO,
            'nome' => 'Atestado',
        ]);
        $tipo->documentoRegras()->attach($cnhTipo->id, [
            'empresa_id' => $empresa->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'obrigatorio' => false,
            'ordem' => 0,
        ]);
        $tipo->documentoRegras()->attach($atestadoTipo->id, [
            'empresa_id' => $empresa->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'obrigatorio' => true,
            'ordem' => 1,
        ]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente',
            'cpf' => '52998224725',
        ]);

        $processo = Processo::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_processo_id' => $tipo->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'status' => ProcessoStatus::EmMontagem,
        ]);

        $cnhDoc = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->where('documento_tipo_id', $cnhTipo->id)
            ->firstOrFail();
        $atestadoDoc = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->where('documento_tipo_id', $atestadoTipo->id)
            ->firstOrFail();

        $this->assertSame(ProcessoDocumentoStatus::Pendente, $atestadoDoc->status);

        $this->patchJson(
            route('processos.documentos.update', [$processo, $cnhDoc]),
            [
                'status' => ProcessoDocumentoStatus::Pendente->value,
                'data_validade_documento' => now()->addYear()->format('Y-m-d'),
            ],
        )->assertOk();

        $file = UploadedFile::fake()->create('cnh.pdf', 120, 'application/pdf');
        $this->post(
            route('processos.documentos.anexos.store', [$processo, $cnhDoc]),
            ['arquivos' => [$file]],
            ['Accept' => 'application/json'],
        )->assertOk();

        $atestadoDoc->refresh();
        $this->assertSame(ProcessoDocumentoStatus::Dispensado, $atestadoDoc->status);
    }

    public function test_sem_anexo_cnh_validade_nao_dispensa_atestado(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $this->actingAs($user);

        $tipo = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'CHA teste sem anexo',
            'slug' => 'cha-teste-sem-anexo',
            'categoria' => TipoProcessoCategoria::Cha,
        ]);
        $platformTipo = PlatformTipoProcesso::query()->create([
            'nome' => $tipo->nome,
            'slug' => $tipo->slug,
            'categoria' => TipoProcessoCategoria::Cha->value,
            'ativo' => true,
            'ordem' => 0,
        ]);

        $cnhTipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => ChaChecklistDocumentoCodigos::CNH_COM_VALIDADE,
            'nome' => 'CNH',
        ]);
        $atestadoTipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => ChaChecklistDocumentoCodigos::ATESTADO_MEDICO_PSICOFISICO,
            'nome' => 'Atestado',
        ]);
        $tipo->documentoRegras()->attach($cnhTipo->id, [
            'empresa_id' => $empresa->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'obrigatorio' => false,
            'ordem' => 0,
        ]);
        $tipo->documentoRegras()->attach($atestadoTipo->id, [
            'empresa_id' => $empresa->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'obrigatorio' => true,
            'ordem' => 1,
        ]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente',
            'cpf' => '52998224725',
        ]);

        $processo = Processo::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_processo_id' => $tipo->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'status' => ProcessoStatus::EmMontagem,
        ]);

        $cnhDoc = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->where('documento_tipo_id', $cnhTipo->id)
            ->firstOrFail();
        $atestadoDoc = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->where('documento_tipo_id', $atestadoTipo->id)
            ->firstOrFail();

        $this->patchJson(
            route('processos.documentos.update', [$processo, $cnhDoc]),
            [
                'status' => ProcessoDocumentoStatus::Pendente->value,
                'data_validade_documento' => now()->addYear()->format('Y-m-d'),
            ],
        )->assertOk();

        $atestadoDoc->refresh();
        $this->assertSame(ProcessoDocumentoStatus::Pendente, $atestadoDoc->status);
    }

    public function test_cnh_vencida_reverte_atestado_dispensado_para_pendente(): void
    {
        Storage::fake('s3');

        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $this->actingAs($user);

        $tipo = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'CHA teste vencida',
            'slug' => 'cha-teste-vencida',
            'categoria' => TipoProcessoCategoria::Cha,
        ]);
        $platformTipo = PlatformTipoProcesso::query()->create([
            'nome' => $tipo->nome,
            'slug' => $tipo->slug,
            'categoria' => TipoProcessoCategoria::Cha->value,
            'ativo' => true,
            'ordem' => 0,
        ]);

        $cnhTipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => ChaChecklistDocumentoCodigos::CNH_COM_VALIDADE,
            'nome' => 'CNH',
        ]);
        $atestadoTipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => ChaChecklistDocumentoCodigos::ATESTADO_MEDICO_PSICOFISICO,
            'nome' => 'Atestado',
        ]);
        $tipo->documentoRegras()->attach($cnhTipo->id, [
            'empresa_id' => $empresa->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'obrigatorio' => false,
            'ordem' => 0,
        ]);
        $tipo->documentoRegras()->attach($atestadoTipo->id, [
            'empresa_id' => $empresa->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'obrigatorio' => true,
            'ordem' => 1,
        ]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente',
            'cpf' => '52998224725',
        ]);

        $processo = Processo::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_processo_id' => $tipo->id,
            'platform_tipo_processo_id' => $platformTipo->id,
            'status' => ProcessoStatus::EmMontagem,
        ]);

        $cnhDoc = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->where('documento_tipo_id', $cnhTipo->id)
            ->firstOrFail();
        $atestadoDoc = ProcessoDocumento::query()
            ->where('processo_id', $processo->id)
            ->where('documento_tipo_id', $atestadoTipo->id)
            ->firstOrFail();

        $this->patchJson(
            route('processos.documentos.update', [$processo, $cnhDoc]),
            [
                'status' => ProcessoDocumentoStatus::Pendente->value,
                'data_validade_documento' => now()->addYear()->format('Y-m-d'),
            ],
        )->assertOk();

        $file = UploadedFile::fake()->create('cnh.pdf', 80, 'application/pdf');
        $this->post(
            route('processos.documentos.anexos.store', [$processo, $cnhDoc]),
            ['arquivos' => [$file]],
            ['Accept' => 'application/json'],
        )->assertOk();

        $atestadoDoc->refresh();
        $this->assertSame(ProcessoDocumentoStatus::Dispensado, $atestadoDoc->status);

        $this->patchJson(
            route('processos.documentos.update', [$processo, $cnhDoc]),
            [
                'status' => ProcessoDocumentoStatus::Enviado->value,
                'data_validade_documento' => now()->subDay()->format('Y-m-d'),
            ],
        )->assertOk();

        $atestadoDoc->refresh();
        $this->assertSame(ProcessoDocumentoStatus::Pendente, $atestadoDoc->status);
    }
}
