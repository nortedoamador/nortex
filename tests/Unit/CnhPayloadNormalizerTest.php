<?php

namespace Tests\Unit;

use App\Services\Cnh\CnhPayloadNormalizer;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class CnhPayloadNormalizerTest extends TestCase
{
    private function normalizer(): CnhPayloadNormalizer
    {
        return new CnhPayloadNormalizer(new NullLogger);
    }

    public function test_normaliza_json_qr_com_filiacao_aninhada(): void
    {
        $n = $this->normalizer();
        $json = json_encode([
            'nome' => 'TESTE SILVA',
            'cpf' => '52998224725',
            'rg' => '1234567',
            'orgao_emissor' => 'SSP RJ',
            'registro_cnh' => '09876543210',
            'categoria' => 'b',
            'validade' => '2030-05-01',
            'data_nascimento' => '1991-07-22',
            'primeira_habilitacao' => '2016-01-10',
            'naturalidade' => 'Niterói',
            'filiacao' => [
                'pai' => 'PAI NOME',
                'mae' => 'MAE NOME',
            ],
        ], JSON_UNESCAPED_UNICODE);

        $out = $n->normalizeFromQrPayload($json);

        $this->assertSame('Teste Silva', $out['nome']);
        $this->assertSame('529.982.247-25', $out['cpf']);
        $this->assertSame('1234567', $out['documento_identidade_numero']);
        $this->assertStringContainsString('SSP', $out['orgao_emissor'] ?? '');
        $this->assertSame('09876543210', $out['numero_cnh']);
        $this->assertSame('B', $out['categoria_cnh']);
        $this->assertSame('2030-05-01', $out['validade_cnh']);
        $this->assertSame('1991-07-22', $out['data_nascimento']);
        $this->assertSame('2016-01-10', $out['primeira_habilitacao']);
        $this->assertSame('Niterói', $out['naturalidade']);
        $this->assertSame('Pai Nome', $out['nome_pai']);
        $this->assertSame('Mae Nome', $out['nome_mae']);
        $this->assertSame(100, $out['nome_score']);
        $this->assertSame(100, $out['cpf_score']);
        $this->assertSame(100, $out['nascimento_score']);
        $this->assertSame(100, $out['confidence_score']);
    }

    public function test_normaliza_texto_ocr_com_cpfs_e_categoria(): void
    {
        $n = $this->normalizer();
        $text = "NOME: Fulano De Tal\nCPF: 111.444.777-35\nCategoria: AB\nValidade: 15/08/2029\n";

        $out = $n->normalizeFromOcrText($text);

        $this->assertSame('Fulano De Tal', $out['nome']);
        $this->assertSame('111.444.777-35', $out['cpf']);
        $this->assertSame('AB', $out['categoria_cnh']);
        $this->assertSame('2029-08-15', $out['validade_cnh']);
        $this->assertSame(70, $out['confidence_score']);
        $this->assertSame(70, $out['nome_score']);
        $this->assertSame(100, $out['cpf_score']);
        $this->assertSame(0, $out['nascimento_score']);
        $this->assertTrue($n->hasMinimumData($out));
    }

    public function test_normaliza_layout_visual_cnh_ocr_com_ruido(): void
    {
        $n = $this->normalizer();
        $text = <<<'TXT'
NOME E SOBRENOME 4º HABILITAÇÃO (PEDRO HENRIQUE ARAUJO BORGES )[2o/06r005 ] 3 DATA, LOCAL E UF DE NASCIMENTO [0703n987, GOIA
CPF 529.982.247-25
TXT;

        $out = $n->normalizeFromOcrText($text);

        $this->assertSame('Pedro Henrique Araujo Borges', $out['nome']);
        $this->assertSame('529.982.247-25', $out['cpf']);
        $this->assertSame('1987-03-07', $out['data_nascimento']);
        $this->assertSame('2005-06-20', $out['primeira_habilitacao']);
        $this->assertSame('2005-06-20', $out['validade']);
        $this->assertSame(100, $out['confidence_score']);
        $this->assertSame(100, $out['nome_score']);
        $this->assertSame(100, $out['cpf_score']);
        $this->assertSame(100, $out['nascimento_score']);
        $this->assertTrue($n->hasMinimumData($out));
        $this->assertTrue($n->hasAnyMeaningfulField($out));
    }

    public function test_preprocess_ocr_corrige_datas_e_digitos(): void
    {
        $n = $this->normalizer();
        $raw = '0703n987 2o/06r005 lixo!@#';
        $prep = $n->preprocessOcrText($raw);

        $this->assertStringContainsString('07', $prep);
        $this->assertStringContainsString('03', $prep);
        $this->assertStringContainsString('987', $prep);
        $this->assertStringContainsString('20/06', $prep);
    }

    public function test_ocr_sujo_minimo_sem_rotulos_completos(): void
    {
        $n = $this->normalizer();
        $text = 'PEDRO HENRIQUE ARAUJO BORGES )[2o/06r005 ] 0703n987';

        $out = $n->normalizeFromOcrText($text);

        $this->assertSame('Pedro Henrique Araujo Borges', $out['nome']);
        $this->assertSame('1987-03-07', $out['data_nascimento']);
        $this->assertSame('2005-06-20', $out['primeira_habilitacao']);
        $this->assertNull($out['validade_cnh']);
        $this->assertSame('2005-06-20', $out['validade']);
        $this->assertSame(60, $out['confidence_score']);
        $this->assertSame(70, $out['nome_score']);
        $this->assertSame(0, $out['cpf_score']);
        $this->assertSame(100, $out['nascimento_score']);
        $this->assertTrue($n->hasMinimumData($out));
    }

    public function test_validade_alias_espelha_validade_cnh_quando_existe(): void
    {
        $n = $this->normalizer();
        $text = "NOME: X\nCPF: 111.444.777-35\nValidade: 15/08/2029\n";

        $out = $n->normalizeFromOcrText($text);

        $this->assertSame('2029-08-15', $out['validade_cnh']);
        $this->assertSame('2029-08-15', $out['validade']);
    }

    public function test_has_minimum_data_apenas_nome(): void
    {
        $n = $this->normalizer();
        $out = [
            'nome' => 'Fulano',
            'cpf' => null,
            'data_nascimento' => null,
            'confidence_score' => 40,
        ];
        $this->assertTrue($n->hasMinimumData($out));
    }

    public function test_has_minimum_data_vazio(): void
    {
        $n = $this->normalizer();
        $base = $n->normalizeFromOcrText('   ');
        $this->assertFalse($n->hasMinimumData($base));
        $this->assertSame(0, $base['confidence_score']);
    }

    public function test_ocr_sujo_nome_cpf_data_nascimento_mes_ocr_invertido(): void
    {
        $n = $this->normalizer();
        $text = 'HENRIQUE CARRIJO FREITAS ] CPF 086.766.576-96 [15/21/1987]';

        $out = $n->normalizeFromOcrText($text);

        $this->assertSame('Henrique Carrijo Freitas', $out['nome']);
        $this->assertSame('086.766.576-96', $out['cpf']);
        $this->assertSame('1987-12-15', $out['data_nascimento']);
        $this->assertSame(70, $out['nome_score']);
        $this->assertSame(100, $out['cpf_score']);
        $this->assertSame(50, $out['nascimento_score']);
        $this->assertSame(100, $out['confidence_score']);
        $this->assertTrue($n->hasMinimumData($out));
    }
}
