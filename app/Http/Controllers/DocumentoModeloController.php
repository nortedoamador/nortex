<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DocumentoModelo;
use App\Models\Empresa;
use App\Support\DocumentoModeloFicheiroUpload;
use App\Support\DocumentoModeloPadraoFicheiro;
use App\Support\DocumentoModeloSincroniaDiscoBd;
use App\Support\DocumentoModeloTemplateBladeScan;
use App\Support\DocumentoModeloTemplateAnexo5dPreamble;
use App\Support\TenantEmpresaContext;
use App\Support\DocumentoModeloTemplatePdf24ImpressaoA4;
use App\Support\DocumentoModeloTemplatePosUpload;
use App\Support\DocumentoModeloTemplateSpanBinder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class DocumentoModeloController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $empresaId = TenantEmpresaContext::empresaId($request);

        $request->validate([
            'titulo' => ['required', 'string', 'max:160'],
            'referencia' => ['nullable', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:80'],
            'arquivo' => ['required', 'file', 'max:15360'],
        ]);

        /** @var UploadedFile $arquivo */
        $arquivo = $request->file('arquivo');
        $lido = DocumentoModeloFicheiroUpload::lerConteudoValidado($arquivo);
        if (isset($lido['error'])) {
            return back()->withErrors(['arquivo' => $lido['error']])->withInput();
        }

        $raw = $lido['content'];
        $posUpload = DocumentoModeloTemplatePosUpload::processar($raw);

        $base = DocumentoModeloFicheiroUpload::normalizarSlugCandidato($request->input('slug'), (string) $request->input('titulo'));
        if ($base === '') {
            return back()->withErrors([
                'titulo' => __('Não foi possível gerar um identificador (slug). Indique um slug manualmente.'),
            ])->withInput();
        }
        if (strlen($base) > 80) {
            return back()->withErrors(['slug' => __('O identificador (slug) não pode exceder 80 caracteres.')])->withInput();
        }
        if (DocumentoModelo::query()->withoutGlobalScope('empresa')->where('empresa_id', $empresaId)->where('slug', $base)->exists()) {
            return back()->withErrors(['slug' => __('Já existe um modelo com este slug.')])->withInput();
        }

        $modelo = DocumentoModelo::query()->create([
            'empresa_id' => $empresaId,
            'slug' => $base,
            'titulo' => $request->input('titulo'),
            'referencia' => filled($request->input('referencia')) ? (string) $request->input('referencia') : null,
            'conteudo' => $raw,
            'conteudo_upload_bruto' => $raw,
            'upload_mapeamento_pendente' => true,
            'mapeamento_upload' => $posUpload['mapeamento_upload'],
        ]);

        $status = __('Modelo criado. Compare o upload à direita e confirme o mapeamento para gravar variáveis Blade e o ficheiro em disco (:slug).', ['slug' => $base]);

        return redirect()
            ->to(tenant_doc_modelo_route('verificacao', ['modelo' => $modelo]))
            ->with('status', $status);
    }

    public function verificacao(Request $request, ?Empresa $empresa = null, DocumentoModelo $modelo): View
    {
        $user = auth()->user();
        abort_unless($user && TenantEmpresaContext::canAccessDocumentoModeloVerificacao($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        abort_unless((int) $modelo->empresa_id === $empresaId, 404);

        DocumentoModeloSincroniaDiscoBd::aplicar($modelo);
        $modelo->refresh();

        $mapeamento = $modelo->mapeamento_upload;
        $itens = [];
        if (is_array($mapeamento) && isset($mapeamento['itens']) && is_array($mapeamento['itens'])) {
            $itens = $mapeamento['itens'];
        }
        $geradoEm = is_array($mapeamento) && isset($mapeamento['gerado_em']) && is_string($mapeamento['gerado_em'])
            ? $mapeamento['gerado_em']
            : null;

        $pendente = (bool) $modelo->upload_mapeamento_pendente;
        $bruto = $modelo->conteudo_upload_bruto;
        $bladeArmazenadoCompleto = ($bruto !== null && $bruto !== '')
            ? (string) $bruto
            : (string) $modelo->conteudo;

        $conteudoComVariaveis = $pendente
            ? DocumentoModeloTemplatePosUpload::processar($bladeArmazenadoCompleto)['html']
            : (string) $modelo->conteudo;

        $scan = DocumentoModeloTemplateBladeScan::analisar($conteudoComVariaveis);
        $bladeComReferenciasCompleto = DocumentoModeloTemplateBladeScan::anotarComFontesDados($conteudoComVariaveis);

        $urlPreviewHtml = null;
        $urlPreviewPdf = null;
        $clienteId = $request->query('cliente_id');
        if ($clienteId !== null && $clienteId !== '' && ctype_digit((string) $clienteId)) {
            $cliente = Cliente::query()
                ->where('empresa_id', $empresaId)
                ->whereKey((int) $clienteId)
                ->first();
            if ($cliente !== null) {
                $q = ['format' => 'html'];
                $emb = $request->query('embarcacao_id');
                if ($emb !== null && $emb !== '' && ctype_digit((string) $emb)) {
                    $q['contexto_id'] = (int) $emb;
                }
                $base = route('clientes.documento-modelos.render', [$cliente, $modelo->slug]);
                $urlPreviewHtml = $base.'?'.http_build_query($q);
                $urlPreviewPdf = $base.'?'.http_build_query(array_merge($q, ['format' => 'pdf']));
            }
        }

        $podeEditarModelo = TenantEmpresaContext::canEditDocumentoModeloConteudo($user, $request);

        return view('documento-modelos.verificacao', [
            'modelo' => $modelo,
            'itens' => $itens,
            'geradoEm' => $geradoEm,
            'scan' => $scan,
            'bladeArmazenadoCompleto' => $bladeArmazenadoCompleto,
            'bladeComReferenciasCompleto' => $bladeComReferenciasCompleto,
            'mapeamentoPendente' => $pendente,
            'urlPreviewHtml' => $urlPreviewHtml,
            'urlPreviewPdf' => $urlPreviewPdf,
            'podeEditarModelo' => $podeEditarModelo,
        ]);
    }

    public function confirmarMapeamentoUpload(Request $request, ?Empresa $empresa = null, DocumentoModelo $modelo): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && TenantEmpresaContext::canEditDocumentoModeloConteudo($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        abort_unless((int) $modelo->empresa_id === $empresaId, 404);

        $queryVerificacao = array_filter(
            $request->only('cliente_id', 'embarcacao_id', 'sort', 'dir'),
            static fn ($v) => $v !== null && $v !== '',
        );

        if (! $modelo->upload_mapeamento_pendente) {
            return redirect()
                ->to(tenant_doc_modelo_route('verificacao', array_merge(['modelo' => $modelo], $queryVerificacao)))
                ->with('status', __('Este modelo já tem o mapeamento confirmado.'));
        }

        $raw = (($modelo->conteudo_upload_bruto !== null && $modelo->conteudo_upload_bruto !== '')
            ? (string) $modelo->conteudo_upload_bruto
            : (string) $modelo->conteudo);

        $out = DocumentoModeloTemplatePosUpload::processar($raw);
        $map = $out['mapeamento_upload'];
        $map['confirmado_em'] = now()->toIso8601String();

        $modelo->update([
            'conteudo' => $out['html'],
            'upload_mapeamento_pendente' => false,
            'mapeamento_upload' => $map,
        ]);

        $diskErr = DocumentoModeloPadraoFicheiro::gravar($modelo->slug, $out['html']);
        $status = __('Mapeamento confirmado. Modelo actualizado e ficheiro gravado em disco.');
        if ($diskErr !== null) {
            $status = __('Mapeamento confirmado na base de dados, mas o ficheiro em disco não foi gravado: :erro', ['erro' => $diskErr]);
        }

        return redirect()
            ->to(tenant_doc_modelo_route('verificacao', array_merge(['modelo' => $modelo], $queryVerificacao)))
            ->with('status', $status);
    }

    public function edit(Request $request, ?Empresa $empresa = null, DocumentoModelo $modelo): View
    {
        $user = auth()->user();
        abort_unless($user && TenantEmpresaContext::canEditDocumentoModeloConteudo($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        abort_unless((int) $modelo->empresa_id === $empresaId, 404);

        DocumentoModeloSincroniaDiscoBd::aplicar($modelo);

        $caminhoPadrao = resource_path('views/documento-modelos/defaults/'.$modelo->slug.'.blade.php');
        $existePadrao = is_readable($caminhoPadrao);
        $caminhoPadraoRelativo = 'resources/views/documento-modelos/defaults/'.$modelo->slug.'.blade.php';

        $normalizarNl = static fn (string $s): string => DocumentoModeloSincroniaDiscoBd::normalizarQuebrasLinha($s);
        $conteudoFicheiroDivergeDaBd = false;
        if ($existePadrao) {
            $noDisco = @file_get_contents($caminhoPadrao);
            if (is_string($noDisco)) {
                $conteudoFicheiroDivergeDaBd = $normalizarNl($noDisco) !== $normalizarNl((string) $modelo->conteudo);
            }
        }

        return view('documento-modelos.edit', compact(
            'modelo',
            'existePadrao',
            'caminhoPadraoRelativo',
            'conteudoFicheiroDivergeDaBd',
        ));
    }

    public function syncPadraoParaDisco(Request $request, ?Empresa $empresa = null, DocumentoModelo $modelo): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && TenantEmpresaContext::canEditDocumentoModeloConteudo($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        abort_unless((int) $modelo->empresa_id === $empresaId, 404);

        $diskErr = DocumentoModeloPadraoFicheiro::gravar($modelo->slug, (string) $modelo->conteudo);
        if ($diskErr !== null) {
            return back()->withErrors(['conteudo' => $diskErr]);
        }

        return back()->with('status', __('Ficheiro :path actualizado com o conteúdo guardado na base de dados.', ['path' => $modelo->slug.'.blade.php']));
    }

    public function update(Request $request, ?Empresa $empresa = null, DocumentoModelo $modelo): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && TenantEmpresaContext::canEditDocumentoModeloConteudo($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        abort_unless((int) $modelo->empresa_id === $empresaId, 404);

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:160'],
            'referencia' => ['nullable', 'string', 'max:160'],
            'conteudo' => ['required', 'string'],
        ]);
        $data['referencia'] = filled($data['referencia'] ?? null) ? (string) $data['referencia'] : null;
        $data['conteudo'] = DocumentoModeloTemplatePdf24ImpressaoA4::injectarSeNecessario(
            DocumentoModeloTemplateAnexo5dPreamble::prependSeNecessario(
                DocumentoModeloTemplateSpanBinder::aplicar((string) $data['conteudo'])
            )
        );
        $data['conteudo_upload_bruto'] = null;
        $data['upload_mapeamento_pendente'] = false;

        $modelo->update($data);

        $diskErr = DocumentoModeloPadraoFicheiro::gravar($modelo->slug, (string) $data['conteudo']);
        $status = __('Modelo atualizado.');
        if ($diskErr !== null) {
            $status .= ' '.$diskErr;
        }

        return back()->with('status', $status);
    }

    public function duplicate(Request $request, DocumentoModelo $modelo): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && TenantEmpresaContext::canEditDocumentoModeloConteudo($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        abort_unless((int) $modelo->empresa_id === $empresaId, 404);

        DocumentoModeloSincroniaDiscoBd::aplicar($modelo);
        $baseSlug = $modelo->slug.'-copia';
        $slug = $baseSlug;
        $n = 2;
        while (
            DocumentoModelo::query()
                ->withoutGlobalScope('empresa')
                ->where('empresa_id', $empresaId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$n;
            $n++;
        }

        $novo = DocumentoModelo::query()->create([
            'empresa_id' => $empresaId,
            'slug' => $slug,
            'titulo' => $modelo->titulo.' ('.__('cópia').')',
            'referencia' => $modelo->referencia,
            'conteudo' => $modelo->conteudo,
            'conteudo_upload_bruto' => $modelo->conteudo_upload_bruto,
            'upload_mapeamento_pendente' => $modelo->upload_mapeamento_pendente,
            'mapeamento_upload' => $modelo->mapeamento_upload,
        ]);

        $diskErr = null;
        if (! $novo->upload_mapeamento_pendente) {
            $diskErr = DocumentoModeloPadraoFicheiro::gravar($slug, (string) $novo->conteudo);
        }
        $status = __('Modelo duplicado. Revise o slug e o título antes de usar em produção.');
        if ($novo->upload_mapeamento_pendente) {
            $status .= ' '.__('Confirme o mapeamento na verificação para gravar o ficheiro em disco.');
        } elseif ($diskErr !== null) {
            $status .= ' '.$diskErr;
        }

        return redirect()
            ->to(tenant_doc_modelo_route('edit', ['modelo' => $novo]))
            ->with('status', $status);
    }

    public function restoreDefault(Request $request, ?Empresa $empresa = null, DocumentoModelo $modelo): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user && TenantEmpresaContext::canEditDocumentoModeloConteudo($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        abort_unless((int) $modelo->empresa_id === $empresaId, 404);

        $path = resource_path('views/documento-modelos/defaults/'.$modelo->slug.'.blade.php');
        if (! is_readable($path)) {
            return back()->withErrors(['conteudo' => __('Não existe ficheiro padrão para este slug (:s).', ['s' => $modelo->slug])]);
        }

        $conteudo = file_get_contents($path);
        if ($conteudo === false) {
            return back()->withErrors(['conteudo' => __('Não foi possível ler o ficheiro padrão.')]);
        }

        $modelo->update([
            'conteudo' => $conteudo,
            'conteudo_upload_bruto' => $conteudo,
            'upload_mapeamento_pendente' => false,
        ]);

        return back()->with('status', __('Conteúdo reposto a partir do modelo padrão do sistema.'));
    }
}
