<?php

namespace App\Support;

use Illuminate\Http\Response;

final class AnexoPrintHtml
{
    /**
     * Página HTML que embute o anexo em iframe e abre a impressão após o carregamento
     * (evita folha em branco quando window.print() dispara antes do PDF/imagem).
     */
    public static function response(string $publicUrl, string $nomeOriginal): Response
    {
        $nome = e($nomeOriginal);
        $src = htmlspecialchars($publicUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{$nome}</title>
    <style>
      html, body { height: 100%; margin: 0; }
      .wrap { height: 100%; display: flex; flex-direction: column; }
      .bar { padding: 10px 12px; font: 14px system-ui, -apple-system, Segoe UI, Roboto, Arial; border-bottom: 1px solid #e5e7eb; }
      .frame { flex: 1; min-height: 0; }
      iframe { width: 100%; height: 100%; border: 0; display: block; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <div class="bar">{$nome}</div>
      <div class="frame">
        <iframe src="{$src}" title="{$nome}" id="anexo-print-frame"></iframe>
      </div>
    </div>
    <script>
      (function () {
        var frame = document.getElementById('anexo-print-frame');
        if (!frame) return;
        frame.addEventListener('load', function () {
          setTimeout(function () {
            try {
              window.focus();
              window.print();
            } catch (e) {}
          }, 300);
        });
      })();
    </script>
  </body>
</html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
