<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tesseract (OCR) — opcional
    |--------------------------------------------------------------------------
    |
    | Caminho absoluto do executável `tesseract` no servidor. Se vazio, o
    | fluxo usa apenas QR + heurísticas limitadas de texto.
    |
    */
    'tesseract_path' => env('CNH_TESSERACT_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | zbarimg (QR Code)
    |--------------------------------------------------------------------------
    |
    | Caminho absoluto do executável zbarimg (pacote ZBar). Usado antes do OCR.
    |
    */
    'zbarimg_path' => env('CNH_ZBARIMG_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Ghostscript (opcional — referência / diagnóstico)
    |--------------------------------------------------------------------------
    |
    | Usado pelo ecossistema Imagick para PDF→raster; mantenha no PATH ou
    | documente aqui o caminho para verificação manual (nx:verify-cn-deps).
    |
    */
    'ghostscript_path' => env('CNH_GHOSTSCRIPT_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | PDF e rasterização
    |--------------------------------------------------------------------------
    |
    | Rasterização da 1.ª página: PHP Imagick + Ghostscript no servidor.
    | Texto embutido no PDF (pdfparser) é tentado só como último recurso.
    |
    */

];
