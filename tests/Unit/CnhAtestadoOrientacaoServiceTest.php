<?php

namespace Tests\Unit;

use App\Services\Marinha\CnhAtestadoOrientacaoService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class CnhAtestadoOrientacaoServiceTest extends TestCase
{
    private CnhAtestadoOrientacaoService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new CnhAtestadoOrientacaoService;
    }

    public function test_cnh_valida_quando_validade_igual_ou_posterior_a_referencia(): void
    {
        $ref = Carbon::parse('2026-03-15');
        $this->assertTrue($this->svc->cnhValidaNaReferencia(Carbon::parse('2026-03-15'), $ref));
        $this->assertTrue($this->svc->cnhValidaNaReferencia(Carbon::parse('2027-01-01'), $ref));
    }

    public function test_cnh_invalida_quando_validade_anterior_a_referencia(): void
    {
        $ref = Carbon::parse('2026-03-15');
        $this->assertFalse($this->svc->cnhValidaNaReferencia(Carbon::parse('2026-03-14'), $ref));
    }

    public function test_sem_validade_nao_dispensa_atestado(): void
    {
        $this->assertTrue($this->svc->atestadoAindaNecessarioPorRegraCnh(null));
        $this->assertFalse($this->svc->cnhDispensaAtestadoMedico(null));
    }

    public function test_orientacao_cnh_vencida_e_aviso(): void
    {
        $linhas = $this->svc->orientacoesParaChecklist(
            'CNH',
            Carbon::parse('2020-01-01'),
            Carbon::parse('2026-01-01'),
        );
        $this->assertCount(1, $linhas);
        $this->assertSame('warning', $linhas[0]['nivel']);
        $this->assertStringContainsString('NÃO dispensa', $linhas[0]['texto']);
    }

    public function test_orientacao_cnh_valida_dispensa_informativa(): void
    {
        $linhas = $this->svc->orientacoesParaChecklist(
            'CNH',
            Carbon::parse('2030-12-31'),
            Carbon::parse('2026-01-01'),
        );
        $this->assertCount(1, $linhas);
        $this->assertSame('info', $linhas[0]['nivel']);
        $this->assertStringContainsString('dispensar atestado', $linhas[0]['texto']);
    }
}
