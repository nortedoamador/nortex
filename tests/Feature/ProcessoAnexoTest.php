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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessoAnexoTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_upload_marks_checklist_item_as_enviado(): void
    {
        Storage::fake('public');

        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user);

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
            'cliente_id' => $cliente->id,
            'tipo_processo_id' => $tipo->id,
            'status' => ProcessoStatus::EmMontagem,
        ]);

        $documento = $processo->documentosChecklist()->firstOrFail();
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

        Storage::disk('public')->assertExists($documento->anexos->first()->path);
    }
}
