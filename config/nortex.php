<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ARA — Anexo 5-E (PDF): nome do diretor / responsável
    |--------------------------------------------------------------------------
    |
    | Preencha aqui ou ligue `AulaNauticaAraPdfData::resolveDiretorNome` a um
    | campo da empresa quando existir na base.
    |
    */
    'ara_pdf_diretor_nome' => env('NORTEX_ARA_PDF_DIRETOR_NOME', ''),

    /*
    |--------------------------------------------------------------------------
    | Antivírus (ClamAV) — desligado por padrão
    |--------------------------------------------------------------------------
    |
    | Quando ativo, o job de validação tenta `clamscan` no caminho absoluto do
    | arquivo (requer ClamAV instalado e no PATH do worker PHP).
    |
    */
    'anexos' => [
        'clamav_enabled' => env('NORTEX_CLAMAV_ENABLED', false),
        'clamav_binary' => env('NORTEX_CLAMAV_BINARY', 'clamscan'),
        'signed_url_ttl_minutes' => env('NORTEX_ANEXOS_SIGNED_URL_TTL_MINUTES', 60),
    ],

];
