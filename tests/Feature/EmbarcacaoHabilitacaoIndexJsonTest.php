<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class EmbarcacaoHabilitacaoIndexJsonTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_embarcacoes_index_json_includes_expected_payload(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->getJson(route('embarcacoes.index'))
            ->assertOk()
            ->assertJsonStructure([
                'count_text',
                'tags_html',
                'list_html',
                'pagination_html',
            ]);
    }

    public function test_habilitacoes_index_json_includes_expected_payload(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->getJson(route('habilitacoes.index'))
            ->assertOk()
            ->assertJsonStructure([
                'count_text',
                'tags_html',
                'list_html',
                'pagination_html',
            ]);
    }

    public function test_habilitacoes_index_html_renders_without_exception(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('habilitacoes.index'))
            ->assertOk();
    }

    public function test_embarcacoes_index_html_renders_without_exception(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('embarcacoes.index'))
            ->assertOk();
    }
}
