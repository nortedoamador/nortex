<?php

namespace App\Enums;

enum ProcessoDocumentoStatus: string
{
    case Pendente = 'pendente';
    case Enviado = 'enviado';
    /** Documento será entregue em papel/presencialmente; sem upload digital. */
    case Fisico = 'fisico';
    case Dispensado = 'dispensado';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => __('Pendente'),
            self::Enviado => __('Enviado'),
            self::Fisico => __('Físico (sem upload)'),
            self::Dispensado => __('Dispensado'),
        };
    }
}
