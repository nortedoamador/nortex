<?php

namespace Tests\Feature;

use App\Enums\ProcessoDocumentoStatus;
use App\Enums\ProcessoStatus;
use App\Enums\TipoProcessoCategoria;
use App\Models\Cliente;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\Processo;
use App\Models\TipoProcesso;
use App\Models\User;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class ProcessoStatusChangeTest extends TestCase
{
    use SafeRefreshDatabase;

    private function processoEmMontagemComChecklistPendente(): array
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $tipo = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Tipo status',
            'slug' => 'tipo-status',
            'categoria' => TipoProcessoCategoria::Embarcacao,
        ]);

        $docTipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'OBR',
            'nome' => 'Doc obrigatório',
        ]);

        $tipo->documentoRegras()->attach($docTipo->id, ['obrigatorio' => true, 'ordem' => 0]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente',
            'cpf' => '52998224725',
        ]);

        $processo = Processo::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_processo_id' => $tipo->id,
            'status' => ProcessoStatus::EmMontagem,
        ]);

        $processo->load('documentosChecklist');
        $this->assertTrue($processo->documentosChecklist->isNotEmpty());
        $this->assertSame('pendente', $processo->documentosChecklist->first()->status->value);

        return [$user, $processo];
    }

    public function test_em_montagem_com_obrigatorios_pendentes_bloqueia_sem_confirmacao(): void
    {
        [$user, $processo] = $this->processoEmMontagemComChecklistPendente();

        $this->actingAs($user)
            ->patchJson(route('processos.status', $processo), [
                'status' => ProcessoStatus::Protocolado->value,
            ])
            ->assertStatus(422);

        $this->assertSame(ProcessoStatus::EmMontagem, $processo->fresh()->status);
    }

    public function test_com_obrigatorios_pendentes_permite_alteracao_apos_ciencia_confirmada(): void
    {
        [$user, $processo] = $this->processoEmMontagemComChecklistPendente();

        $this->actingAs($user)
            ->patchJson(route('processos.status', $processo), [
                'status' => ProcessoStatus::Protocolado->value,
                'confirmar_ciencia_pendencias_documentais' => true,
            ])
            ->assertOk()
            ->assertJsonPath('status', ProcessoStatus::Protocolado->value);

        $this->assertSame(ProcessoStatus::Protocolado, $processo->fresh()->status);
    }

    public function test_em_montagem_com_obrigatorio_enviado_sem_anexo_bloqueia_sem_confirmacao(): void
    {
        [$user, $processo] = $this->processoEmMontagemComChecklistPendente();
        $doc = $processo->documentosChecklist->first();
        $doc->update(['status' => ProcessoDocumentoStatus::Enviado]);

        $this->actingAs($user)
            ->patchJson(route('processos.status', $processo), [
                'status' => ProcessoStatus::Protocolado->value,
            ])
            ->assertStatus(422);

        $this->assertSame(ProcessoStatus::EmMontagem, $processo->fresh()->status);
    }

    public function test_fora_de_montagem_com_obrigatorios_pendentes_permite_sem_confirmacao(): void
    {
        [$user, $processo] = $this->processoEmMontagemComChecklistPendente();
        $processo->update(['status' => ProcessoStatus::Protocolado]);

        $this->actingAs($user)
            ->patchJson(route('processos.status', $processo), [
                'status' => ProcessoStatus::EmAndamento->value,
            ])
            ->assertOk()
            ->assertJsonPath('status', ProcessoStatus::EmAndamento->value);

        $this->assertSame(ProcessoStatus::EmAndamento, $processo->fresh()->status);
    }

    public function test_aguardando_prova_bloqueado_para_tipo_que_nao_e_arrais(): void
    {
        [$user, $processo] = $this->processoEmMontagemComChecklistPendente();

        $this->actingAs($user)
            ->patchJson(route('processos.status', $processo), [
                'status' => ProcessoStatus::AguardandoProva->value,
                'confirmar_ciencia_pendencias_documentais' => true,
            ])
            ->assertStatus(422);

        $this->assertSame(ProcessoStatus::EmMontagem, $processo->fresh()->status);
    }

    public function test_aguardando_prova_permitido_para_inscricao_arrais_amador(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $tipo = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Inscrição e emissão de Arrais-Amador',
            'slug' => 'cha-inscricao-arrais-amador',
            'categoria' => TipoProcessoCategoria::Cha,
        ]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Cliente',
            'cpf' => '52998224725',
        ]);

        $processo = Processo::withoutEvents(fn () => Processo::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_processo_id' => $tipo->id,
            'status' => ProcessoStatus::EmAndamento,
        ]));

        $this->actingAs($user)
            ->patchJson(route('processos.status', $processo), [
                'status' => ProcessoStatus::AguardandoProva->value,
            ])
            ->assertOk()
            ->assertJsonPath('status', ProcessoStatus::AguardandoProva->value);

        $this->assertSame(ProcessoStatus::AguardandoProva, $processo->fresh()->status);
    }
}
