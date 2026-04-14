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
use App\Support\FileEncryption;
use Tests\Concerns\SafeRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessoAnexoTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_upload_marks_checklist_item_as_enviado(): void
    {
        Storage::fake('s3');

        ['user' => $user, 'processo' => $processo, 'documento' => $documento] = $this->criarContextoProcesso();
        $this->actingAs($user);
        $this->assertSame(ProcessoDocumentoStatus::Pendente, $documento->status);

        $file = UploadedFile::fake()->create('atestado.pdf', 120, 'application/pdf');

        $response = $this->post(
            route('processos.documentos.anexos.store', [$processo, $documento]),
            ['arquivos' => [$file]],
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $documento->refresh();
        $this->assertSame(ProcessoDocumentoStatus::Enviado, $documento->status);
        $this->assertCount(1, $documento->anexos);

        Storage::disk('s3')->assertExists($documento->anexos->first()->path);
    }

    public function test_upload_stores_encrypted_payload_and_inline_returns_plain_contents(): void
    {
        Storage::fake('s3');

        ['user' => $user, 'processo' => $processo, 'documento' => $documento] = $this->criarContextoProcesso();
        $this->actingAs($user);

        $plain = 'pdf-content-for-encryption-check';
        $file = UploadedFile::fake()->createWithContent('atestado.pdf', $plain);

        $this->post(
            route('processos.documentos.anexos.store', [$processo, $documento]),
            ['arquivos' => [$file]],
        )->assertRedirect();

        $anexo = $documento->fresh()->anexos()->firstOrFail();
        $raw = Storage::disk('s3')->get($anexo->path);

        $this->assertNotSame($plain, $raw);
        $this->assertSame($plain, FileEncryption::decrypt($raw));

        $response = $this->get($anexo->signedInlineUrl());

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertSame($plain, $response->getContent());

        $this->get(route('processos.documentos.anexos.inline', ['anexo' => $anexo]))
            ->assertForbidden();
    }

    /**
     * @return array{user: User, processo: Processo, documento: \App\Models\ProcessoDocumento}
     */
    private function criarContextoProcesso(): array
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $tipo = TipoProcesso::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Tipo',
            'slug' => 'tipo',
            'categoria' => TipoProcessoCategoria::Embarcacao,
        ]);

        $docTipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'RG',
            'nome' => 'RG',
        ]);

        $tipo->documentoRegras()->attach($docTipo->id, ['obrigatorio' => true, 'ordem' => 0]);

        $cliente = Cliente::query()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'C',
        ]);

        $processo = Processo::query()->create([
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'tipo_processo_id' => $tipo->id,
            'status' => ProcessoStatus::EmMontagem,
        ]);

        return [
            'user' => $user,
            'processo' => $processo,
            'documento' => $processo->documentosChecklist()->firstOrFail(),
        ];
    }
}
