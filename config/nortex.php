<?php

return [

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
    ],

];
