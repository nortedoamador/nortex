<?php

namespace Tests\Unit;

use App\Support\CpfUfEmitente;
use PHPUnit\Framework\TestCase;

class CpfUfEmitenteTest extends TestCase
{
    public function test_cpf_valido_retorna_uf_pelo_nono_digito(): void
    {
        $this->assertSame('RJ', CpfUfEmitente::ufSugerida('52998224725'));
    }

    public function test_cpf_invalido_retorna_null(): void
    {
        $this->assertNull(CpfUfEmitente::ufSugerida('11111111111'));
    }
}
