<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tamanho máximo de upload (anexos, documentos, etc.)
    |--------------------------------------------------------------------------
    |
    | Valor em kilobytes (KB), como na regra de validação Laravel `max:`.
    | 3072 = 3 MB.
    |
    */
    'max_kb' => (int) env('UPLOAD_MAX_KB', 3072),
];
