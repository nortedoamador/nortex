<?php

namespace Tests\Unit;

use App\Support\NormamDocumentacaoCatalog;
use App\Support\Normam211DocumentoCodigos;
use PHPUnit\Framework\TestCase;

class NormamDocumentacaoCatalogTest extends TestCase
{
    public function test_estrutura_contem_ambas_normas(): void
    {
        $e = NormamDocumentacaoCatalog::estrutura();
        $this->assertArrayHasKey(NormamDocumentacaoCatalog::NORMAM_ESPORTE_RECREIO, $e);
        $this->assertArrayHasKey(NormamDocumentacaoCatalog::NORMAM_MOTO_AQUATICA, $e);
    }

    public function test_entrada_por_codigo_resolve_anexo_1c_cha_mta(): void
    {
        $r = NormamDocumentacaoCatalog::entradaPorCodigoChecklist('CHA_COMPROVANTE_RESIDENCIA_212_2C');
        $this->assertNotNull($r);
        $this->assertSame(NormamDocumentacaoCatalog::NORMAM_MOTO_AQUATICA, $r['norma']);
        $this->assertSame('1-C', $r['anexo']);
        $this->assertSame('anexo-1c-normam212', $r['modelo_slug']);
    }

    public function test_slug_modelo_fallback_residencia_cha_212(): void
    {
        $this->assertSame(
            'anexo-1c-normam212',
            Normam211DocumentoCodigos::slugModeloPorCodigoChecklist(Normam211DocumentoCodigos::CHA_COMPROVANTE_RESIDENCIA_212_1C_LEGACY),
        );
    }

    public function test_texto_resumo_nao_vazio(): void
    {
        $t = NormamDocumentacaoCatalog::textoResumo();
        $this->assertStringContainsString('NORMAM-212', $t);
        $this->assertStringContainsString('NORMAM-211', $t);
        $this->assertStringContainsString('CHA-MTA', $t);
    }

    public function test_entrada_por_codigo_resolve_anexo_3d_extravio_mta(): void
    {
        $r = NormamDocumentacaoCatalog::entradaPorCodigoChecklist(Normam211DocumentoCodigos::CHA_DECL_EXTRAVIO_MTA_3D_212);
        $this->assertNotNull($r);
        $this->assertSame(NormamDocumentacaoCatalog::NORMAM_MOTO_AQUATICA, $r['norma']);
        $this->assertSame('3-D', $r['anexo']);
        $this->assertSame('anexo-3d-extravio-cha-mta-normam212', $r['modelo_slug']);
    }
}
