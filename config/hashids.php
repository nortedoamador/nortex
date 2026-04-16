<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Salt
    |--------------------------------------------------------------------------
    |
    | Deve ser estável em produção (não alterar após URLs já partilhadas).
    | Se vazio, usa um derivado de APP_KEY.
    |
    */
    'salt' => env('HASHIDS_SALT', ''),

    /*
    |--------------------------------------------------------------------------
    | Tamanho mínimo do hash
    |--------------------------------------------------------------------------
    */
    'min_length' => (int) env('HASHIDS_MIN_LENGTH', 8),

];
