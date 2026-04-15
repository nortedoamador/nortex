<?php

namespace App\Support;

use Illuminate\Http\Response;

final class AnexoPrintHtml
{
    /**
     * Página HTML que embute o anexo em iframe e abre a impressão após o carregamento.
     *
     * Em Chromium, imprimir a janela pai com um PDF no iframe costuma gerar pré-visualização
     * partida (faixa escura, recorte). Por isso tentamos primeiro `print()` no documento do
     * iframe; se falhar (PDF bloqueado, cross-origin), voltamos ao print da página,
     * com CSS de impressão que esconde a barra e força o iframe a ocupar a folha inteira.
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
      html, body { height: 100%; margin: 0; background: #fff; }
      .wrap { height: 100%; display: flex; flex-direction: column; }
      .bar {
        flex-shrink: 0;
        padding: 10px 12px;
        font: 14px system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
        border-bottom: 1px solid #e5e7eb;
        color: #111827;
        background: #fff;
      }
      .frame { flex: 1; min-height: 0; position: relative; }
      iframe { width: 100%; height: 100%; border: 0; display: block; background: #fff; }

      @media print {
        .bar { display: none !important; }
        html, body {
          height: auto !important;
          min-height: 100% !important;
          margin: 0 !important;
          padding: 0 !important;
          background: #fff !important;
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }
        .wrap {
          height: auto !important;
          min-height: 100% !important;
          display: block !important;
        }
        .frame {
          position: relative !important;
          height: 100vh !important;
          min-height: 100vh !important;
          margin: 0 !important;
          padding: 0 !important;
        }
        iframe {
          position: absolute !important;
          left: 0 !important;
          top: 0 !important;
          width: 100% !important;
          height: 100% !important;
          max-width: 100% !important;
          margin: 0 !important;
          border: 0 !important;
        }
      }
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

        function runPrint() {
          try {
            var cw = frame.contentWindow;
            if (cw && typeof cw.print === 'function') {
              cw.focus();
              cw.print();
              return;
            }
          } catch (e) {}

          try {
            window.focus();
            window.print();
          } catch (e2) {}
        }

        frame.addEventListener('load', function () {
          setTimeout(runPrint, 450);
        });
      })();
    </script>
  </body>
</html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
