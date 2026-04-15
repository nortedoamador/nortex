<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class PagamentoInicialPendenteTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_dashboard_redireciona_para_pagamento_quando_pendente(): void
    {
        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@pendente.test',
            'pagamento_inicial_pendente' => true,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('assinatura.pagamento-pendente'));
    }

    public function test_utilizador_sem_pendente_acessa_dashboard(): void
    {
        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@ok.test',
            'pagamento_inicial_pendente' => false,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
