<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Services\Cnh\CnhExtractionResult;
use App\Services\CnhExtractorService;
use Tests\Concerns\SafeRefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CnhExtractApiTest extends TestCase
{
    use SafeRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function userComPermissaoClientesManage(): User
    {
        $empresa = Empresa::factory()->create();

        return User::factory()->create(['empresa_id' => $empresa->id]);
    }

    public function test_extracao_cnh_com_qr_retorna_200_e_dados(): void
    {
        $user = $this->userComPermissaoClientesManage();

        $payload = [
            'nome' => 'João Da Silva',
            'cpf' => '529.982.247-25',
            'data_nascimento' => '1990-03-15',
            'documento_identidade_numero' => '123456789',
            'orgao_emissor' => 'SSP/RJ',
            'numero_cnh' => '01234567890',
            'categoria_cnh' => 'B',
            'validade_cnh' => '2030-01-20',
            'primeira_habilitacao' => '2015-06-10',
            'naturalidade' => 'Rio de Janeiro',
            'nome_pai' => 'Pai Exemplo',
            'nome_mae' => 'Mãe Exemplo',
        ];

        $this->mock(CnhExtractorService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn(new CnhExtractionResult(true, $payload, 'qr'));
        });

        $this->actingAs($user)
            ->post(route('api.cnh.extract'), [
                'file' => UploadedFile::fake()->create('cnh.jpg', 10, 'image/jpeg'),
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('source', 'qr')
            ->assertJsonPath('data.cpf', '529.982.247-25')
            ->assertJsonPath('data.numero_cnh', '01234567890');
    }

    public function test_extracao_cnh_sem_qr_com_ocr_retorna_200_e_dados(): void
    {
        $user = $this->userComPermissaoClientesManage();

        $payload = [
            'nome' => 'Maria Costa',
            'cpf' => '111.444.777-35',
            'categoria_cnh' => 'AB',
            'validade_cnh' => '2028-12-01',
            'documento_identidade_numero' => null,
            'data_nascimento' => null,
            'orgao_emissor' => null,
            'numero_cnh' => null,
            'primeira_habilitacao' => null,
            'naturalidade' => null,
            'nome_pai' => null,
            'nome_mae' => null,
        ];

        $this->mock(CnhExtractorService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn(new CnhExtractionResult(true, $payload, 'ocr'));
        });

        $this->actingAs($user)
            ->post(route('api.cnh.extract'), [
                'file' => UploadedFile::fake()->create('foto.png', 10, 'image/png'),
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('source', 'ocr')
            ->assertJsonPath('data.nome', 'Maria Costa');
    }

    public function test_extracao_cnh_imagem_ilegivel_retorna_422(): void
    {
        $user = $this->userComPermissaoClientesManage();

        $this->mock(CnhExtractorService::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn(new CnhExtractionResult(
                    false,
                    null,
                    'none',
                    'Não foi possível ler automaticamente. Preencha manualmente.'
                ));
        });

        $this->actingAs($user)
            ->post(route('api.cnh.extract'), [
                'file' => UploadedFile::fake()->create('ruim.webp', 5, 'image/webp'),
            ])
            ->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('source', 'none');
    }

    public function test_extracao_cnh_usuario_sem_clientes_manage_retorna_403(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $instrutor = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'instrutor')
            ->firstOrFail();
        $user->roles()->sync([$instrutor->id]);

        $this->mock(CnhExtractorService::class, function ($mock) {
            $mock->shouldNotReceive('extract');
        });

        $this->actingAs($user)
            ->post(route('api.cnh.extract'), [
                'file' => UploadedFile::fake()->create('cnh.jpg', 10, 'image/jpeg'),
            ])
            ->assertForbidden();
    }
}
