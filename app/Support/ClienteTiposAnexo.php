<?php

namespace App\Support;

final class ClienteTiposAnexo
{
    public const CNH = 'CNH';

    public const COMPROVANTE_ENDERECO = 'COMPROVANTE_ENDERECO';

    /** Contrato social (PJ), mesmo campo de upload que a CNH na ficha. */
    public const CONTRATO_SOCIAL = 'CONTRATO_SOCIAL';

    public const DOC_REPRESENTANTE_LEGAL = 'DOC_REPRESENTANTE_LEGAL';

    public const CARTAO_CNPJ = 'CARTAO_CNPJ';

    public const COMPROVANTE_INSCRICAO_ESTADUAL = 'COMPROVANTE_INSCRICAO_ESTADUAL';

    public const COMPROVANTE_INSCRICAO_MUNICIPAL = 'COMPROVANTE_INSCRICAO_MUNICIPAL';

    public static function label(?string $codigo): string
    {
        return match ($codigo) {
            self::CNH => __('CNH'),
            self::COMPROVANTE_ENDERECO => __('Comprovante de endereço'),
            self::CONTRATO_SOCIAL => __('Contrato social'),
            self::DOC_REPRESENTANTE_LEGAL => __('Documento do representante legal (RG/CNH)'),
            self::CARTAO_CNPJ => __('Cartão CNPJ'),
            self::COMPROVANTE_INSCRICAO_ESTADUAL => __('Comprovante de Inscrição Estadual'),
            self::COMPROVANTE_INSCRICAO_MUNICIPAL => __('Comprovante de Inscrição Municipal'),
            null, '' => __('Sem tipo'),
            default => $codigo,
        };
    }
}
