<?php

namespace App\Services\Cnh;

use Smalot\PdfParser\Parser;
use Throwable;

/**
 * Extrai texto de PDFs (camada de texto). PDFs só com imagem não devolvem texto.
 */
final class CnhPdfTextExtractor
{
    /**
     * Junta o texto das primeiras páginas (CNH costuma caber na primeira).
     */
    public function extractText(string $absolutePdfPath, int $maxPages = 3): ?string
    {
        if (! is_readable($absolutePdfPath)) {
            return null;
        }

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($absolutePdfPath);
            $pages = $pdf->getPages();
            $chunks = [];
            $n = 0;
            foreach ($pages as $page) {
                if ($n >= $maxPages) {
                    break;
                }
                $chunks[] = $page->getText();
                $n++;
            }
            $text = trim(implode("\n", $chunks));

            return $text === '' ? null : $text;
        } catch (Throwable) {
            return null;
        }
    }
}
