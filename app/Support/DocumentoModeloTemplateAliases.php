<?php

namespace App\Support;

use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Support\Facades\Storage;

/**
 * Aliases legados usados em modelos Blade (ex.: nome_empresa, cpf_cliente) em conjunto com {@see Normam211212TemplateVars}.
 */
final class DocumentoModeloTemplateAliases
{
    /**
     * Texto padrão do parágrafo dos procuradores na procuração (quando a empresa não define um próprio).
     */
    public const TEXTO_PADRAO_PROCURACAO_PROCURADORES = 'Nomeia e constitui os seus bastante procuradores NOME PROCURADOR, CPF: NUMERO CPF portador da carteira de identidade nº RG E ORGAO EMISSOR e NOME PROCURADOR, CPF: NUMERO CPF portador da carteira de identidade nº RG E ORGAO EMISSOR, NOME PROCURADOR, CPF: NUMERO CPF portador da carteira de identidade nº RG E ORGAO EMISSOR, com poderes para representar perante a CP/DL OU AG DA JURISDICAO, quem confere poderes para, bem como, requerer, assinar, Termo de Responsabilidade, BADE, BSADE, BDMOTO, Declaração de Extravio, Residência e de Construção, retirar processos, dar entrada em processos de renovação e retirada de CIR, solicitar segunda via da CHA, apresentar defesa, usando de todos os meios legais para o Fiel cumprimento do presente mandato, bem como SUBSTABELECER com reserva de poderes.';

    /**
     * Texto usado nos modelos (BD em branco = padrão da plataforma).
     */
    public static function textoProcuracaoProcuradoresResolvido(Empresa $empresa): string
    {
        $t = trim((string) ($empresa->texto_procuracao_procuradores ?? ''));

        return $t !== '' ? $t : self::TEXTO_PADRAO_PROCURACAO_PROCURADORES;
    }

    /**
     * @param  array<string, mixed>  $normam
     * @return array<string, mixed>
     */
    public static function paraEmpresaCliente(Empresa $empresa, Cliente $cliente, array $normam): array
    {
        $cidade = trim((string) ($empresa->cidade ?? ''));
        if ($cidade === '') {
            $cidade = trim((string) ($cliente->cidade ?? ''));
        }
        $uf = trim((string) ($empresa->uf ?? $cliente->uf ?? ''));
        $cidadeUfEmpresa = $cidade !== '' && $uf !== ''
            ? $cidade.' - '.$uf
            : ($cidade !== '' ? $cidade : $uf);

        return [
            'nome_empresa' => (string) ($empresa->nome ?? ''),
            'cnpj_empresa' => self::formatarCnpj($empresa->cnpj ?? null),
            'cidade_uf_empresa' => $cidadeUfEmpresa,
            'telefone_empresa' => trim((string) ($empresa->telefone ?? '')),
            'logo_empresa_url' => self::urlLogoPublico($empresa),
            'texto_procuracao_procuradores' => self::textoProcuracaoProcuradoresResolvido($empresa),

            'nome_cliente' => (string) ($normam['nome'] ?? ''),
            'cpf_cliente' => (string) ($normam['cpf'] ?? ''),
            'rg_cliente' => (string) ($normam['rg'] ?? ''),
            'orgao_emissor_cliente' => (string) ($normam['orgao'] ?? ''),
            'endereco_cliente' => (string) ($normam['endereco_completo'] ?? ''),
            'contato_cliente' => (string) ($normam['telefone_email_linha'] ?? ''),
        ];
    }

    /**
     * URL absoluta para uso em &lt;img src&gt; (HTML/PDF).
     */
    public static function urlLogoPublico(Empresa $empresa): ?string
    {
        $path = $empresa->logo_path;
        if ($path === null || $path === '') {
            return null;
        }
        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return url(Storage::disk('public')->url($path));
    }

    private static function formatarCnpj(?string $cnpj): string
    {
        if ($cnpj === null || $cnpj === '') {
            return '—';
        }
        $d = preg_replace('/\D/', '', $cnpj);
        if (strlen($d) === 14) {
            return substr($d, 0, 2).'.'.substr($d, 2, 3).'.'.substr($d, 5, 3).'/'.substr($d, 8, 4).'-'.substr($d, 12, 2);
        }

        return $cnpj;
    }
}
