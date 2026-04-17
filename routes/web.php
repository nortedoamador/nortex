<?php

use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\DocumentoModeloLaboratorioController;
use App\Http\Controllers\Admin\DocumentoTipoAdminController;
use App\Http\Controllers\Admin\EmpresaCompromissoController;
use App\Http\Controllers\Admin\EmpresaSettingsController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TipoProcessoAdminController;
use App\Http\Controllers\AlunoAjaxController;
use App\Http\Controllers\AulaAtestadoController;
use App\Http\Controllers\AulaComunicadoController;
use App\Http\Controllers\AulaEscolaController;
use App\Http\Controllers\AulaNauticaController;
use App\Http\Controllers\AulaNauticaPdfController;
use App\Http\Controllers\ClienteAnexoFileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DocumentoModeloController;
use App\Http\Controllers\DocumentoModeloRenderController;
use App\Http\Controllers\EmbarcacaoAnexoFileController;
use App\Http\Controllers\EmbarcacaoController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\EscolaInstrutorController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\HabilitacaoAnexoFileController;
use App\Http\Controllers\HabilitacaoController;
use App\Http\Controllers\PendingSubscriptionCheckoutController;
use App\Http\Controllers\PlanosController;
use App\Http\Controllers\Platform\AnexoTipoController as PlatformAnexoTipoController;
use App\Http\Controllers\Platform\AuditoriaController as PlatformAuditoriaController;
use App\Http\Controllers\Platform\ChecklistDocumentosController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\DocumentoModeloGlobalController as PlatformDocumentoModeloGlobalController;
use App\Http\Controllers\Platform\DocumentoModeloGlobalLaboratorioController as PlatformDocumentoModeloGlobalLaboratorioController;
use App\Http\Controllers\Platform\DocumentoModeloGlobalPreviewController;
use App\Http\Controllers\Platform\EmpresaAdminUserController as PlatformEmpresaAdminUserController;
use App\Http\Controllers\Platform\EmpresaController as PlatformEmpresaController;
use App\Http\Controllers\Platform\ImpersonateController as PlatformImpersonateController;
use App\Http\Controllers\Platform\MaintenanceController as PlatformMaintenanceController;
use App\Http\Controllers\Platform\SubscriptionAdminController;
use App\Http\Controllers\Platform\TipoProcessoController as PlatformTipoProcessoController;
use App\Http\Controllers\Platform\TipoServicoController as PlatformTipoServicoController;
use App\Http\Controllers\Platform\UsuarioController as PlatformUsuarioController;
use App\Http\Controllers\ProcessoController;
use App\Http\Controllers\ProcessoDocumentoAnexoFileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionSignupController;
use App\Http\Controllers\TourController;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('stripe.webhook');

Route::prefix('assinatura')->name('assinatura.')->group(function () {
    Route::get('/sucesso', [SubscriptionSignupController::class, 'success'])->name('sucesso');
    Route::get('/cancelado', [SubscriptionSignupController::class, 'canceled'])->name('cancelado');
    Route::middleware('guest')->group(function () {
        Route::get('/', [SubscriptionSignupController::class, 'index'])->name('index');
        Route::get('/{plan}', [SubscriptionSignupController::class, 'create'])
            ->whereIn('plan', ['basica', 'completa'])
            ->name('create');
        Route::post('/{plan}', [SubscriptionSignupController::class, 'store'])
            ->whereIn('plan', ['basica', 'completa'])
            ->middleware('throttle:6,1')
            ->name('store');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/assinatura/pagamento-pendente', [PendingSubscriptionCheckoutController::class, 'show'])
        ->name('assinatura.pagamento-pendente');
    Route::post('/assinatura/pagamento-pendente/checkout', [PendingSubscriptionCheckoutController::class, 'startCheckout'])
        ->middleware('throttle:6,1')
        ->name('assinatura.pagamento-pendente.checkout');
});

Route::bind('usuario', function (string $value) {
    $auth = auth()->user();
    abort_unless($auth && $auth->empresa_id, 403);

    return User::query()
        ->where('empresa_id', $auth->empresa_id)
        ->whereKey($value)
        ->firstOrFail();
});

Route::bind('papel', function (string $value) {
    $auth = auth()->user();
    abort_unless($auth, 403);

    if ($auth->is_platform_admin || $auth->is_master_admin) {
        $empresa = request()->route('empresa');
        $empresaId = null;
        if ($empresa instanceof Empresa) {
            $empresaId = (int) $empresa->id;
        } elseif (is_numeric($empresa)) {
            $empresaId = (int) $empresa;
        }
        if ($empresaId !== null && $empresaId > 0) {
            return Role::query()
                ->where('empresa_id', $empresaId)
                ->whereKey($value)
                ->firstOrFail();
        }
    }

    abort_unless($auth->empresa_id, 403);

    return Role::query()
        ->where('empresa_id', $auth->empresa_id)
        ->whereKey($value)
        ->firstOrFail();
});

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if (($user->is_platform_admin || $user->is_master_admin) && ! $user->empresa_id) {
            return redirect()->route('platform.dashboard');
        }

        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', [ProcessoController::class, 'dashboard'])
    ->middleware(['auth', 'verified', 'tenant.empresa'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'tenant.empresa'])->group(function () {
    Route::get('/planos', [PlanosController::class, 'index'])->name('planos.index');
    Route::post('/planos/checkout', [PlanosController::class, 'checkout'])
        ->middleware('throttle:6,1')
        ->name('planos.checkout');
});

Route::get('/clientes/{cliente}/embarcacoes-options', [ClienteController::class, 'embarcacoesOptions'])
    ->middleware(['auth', 'verified', 'tenant.empresa', 'tenant.subscription'])
    ->name('clientes.embarcacoes.options');

Route::get('/clientes/{cliente}/habilitacoes-options', [ClienteController::class, 'habilitacoesOptions'])
    ->middleware(['auth', 'verified', 'tenant.empresa', 'tenant.subscription'])
    ->name('clientes.habilitacoes.options');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('platform')->name('platform.')->middleware(['platform.admin', 'platform.audit'])->group(function () {
        Route::get('/', PlatformDashboardController::class)->name('dashboard');
        Route::post('/manutencao', [PlatformMaintenanceController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('maintenance.update');
        Route::get('/assinaturas/cadastro-manual', [SubscriptionAdminController::class, 'manualCreate'])->name('assinaturas.manual');
        Route::post('/assinaturas/cadastro-manual', [SubscriptionAdminController::class, 'manualStore'])
            ->middleware('throttle:20,1')
            ->name('assinaturas.manual.store');

        Route::get('/assinaturas/adicionar', [SubscriptionAdminController::class, 'adicionarCreate'])->name('assinaturas.adicionar');
        Route::post('/assinaturas/adicionar', [SubscriptionAdminController::class, 'adicionarStore'])
            ->middleware('throttle:12,1')
            ->name('assinaturas.adicionar.store');

        Route::get('/assinaturas', [SubscriptionAdminController::class, 'index'])->name('assinaturas.index');
        Route::post('/assinaturas/{empresa}/sync', [SubscriptionAdminController::class, 'sync'])
            ->middleware('throttle:30,1')
            ->name('assinaturas.sync');
        Route::post('/assinaturas/{empresa}/cancelar-fim-periodo', [SubscriptionAdminController::class, 'cancelarNoFimDoPeriodo'])
            ->middleware('throttle:12,1')
            ->name('assinaturas.cancelar-fim-periodo');
        Route::post('/assinaturas/{empresa}/manter-renovacao', [SubscriptionAdminController::class, 'manterRenovacao'])
            ->middleware('throttle:12,1')
            ->name('assinaturas.manter-renovacao');
        Route::post('/assinaturas/{empresa}/reenviar-senha', [SubscriptionAdminController::class, 'reenviarSenhaAdmin'])
            ->middleware('throttle:12,1')
            ->name('assinaturas.reenviar-senha');

        Route::get('/empresas', [PlatformEmpresaController::class, 'index'])->name('empresas.index');
        Route::get('/empresas/criar', [PlatformEmpresaController::class, 'create'])->name('empresas.create');
        Route::post('/empresas', [PlatformEmpresaController::class, 'store'])->name('empresas.store');
        Route::get('/empresas/{empresa}', [PlatformEmpresaController::class, 'show'])->name('empresas.show');
        Route::get('/empresas/{empresa}/editar', [PlatformEmpresaController::class, 'edit'])->name('empresas.edit');
        Route::patch('/empresas/{empresa}', [PlatformEmpresaController::class, 'update'])->name('empresas.update');

        Route::post('/empresas/{empresa}/usuario-administrador', [PlatformEmpresaAdminUserController::class, 'store'])
            ->name('empresas.admin-user.store');

        Route::prefix('empresas/{empresa}/admin')->name('empresas.admin.')->scopeBindings()->group(function () {
            Route::get('/papeis', [RoleController::class, 'index'])->name('roles.index');
            Route::get('/papeis/criar', [RoleController::class, 'create'])->name('roles.create');
            Route::post('/papeis', [RoleController::class, 'store'])->name('roles.store');
            Route::get('/papeis/{papel}/editar', [RoleController::class, 'edit'])->name('roles.edit');
            Route::patch('/papeis/{papel}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('/papeis/{papel}', [RoleController::class, 'destroy'])->name('roles.destroy');

            Route::get('/cadastros/tipos-processo', [TipoProcessoAdminController::class, 'index'])->name('tipo-processos.index');
            Route::get('/cadastros/tipos-processo/criar', [TipoProcessoAdminController::class, 'create'])->name('tipo-processos.create');
            Route::post('/cadastros/tipos-processo', [TipoProcessoAdminController::class, 'store'])->name('tipo-processos.store');
            Route::get('/cadastros/tipos-processo/{tipo_processo}/editar', [TipoProcessoAdminController::class, 'edit'])->name('tipo-processos.edit');
            Route::patch('/cadastros/tipos-processo/{tipo_processo}', [TipoProcessoAdminController::class, 'update'])->name('tipo-processos.update');
            Route::delete('/cadastros/tipos-processo/{tipo_processo}', [TipoProcessoAdminController::class, 'destroy'])->name('tipo-processos.destroy');
            Route::get('/cadastros/tipos-processo/{tipo_processo}/regras', [TipoProcessoAdminController::class, 'editRegras'])->name('tipo-processos.edit-regras');
            Route::put('/cadastros/tipos-processo/{tipo_processo}/regras', [TipoProcessoAdminController::class, 'updateRegras'])->name('tipo-processos.update-regras');

            Route::get('/cadastros/tipos-documento', [DocumentoTipoAdminController::class, 'index'])->name('documento-tipos.index');
            Route::get('/cadastros/tipos-documento/criar', [DocumentoTipoAdminController::class, 'create'])->name('documento-tipos.create');
            Route::post('/cadastros/tipos-documento', [DocumentoTipoAdminController::class, 'store'])->name('documento-tipos.store');
            Route::get('/cadastros/tipos-documento/{documento_tipo}/editar', [DocumentoTipoAdminController::class, 'edit'])->name('documento-tipos.edit');
            Route::patch('/cadastros/tipos-documento/{documento_tipo}', [DocumentoTipoAdminController::class, 'update'])->name('documento-tipos.update');
            Route::delete('/cadastros/tipos-documento/{documento_tipo}', [DocumentoTipoAdminController::class, 'destroy'])->name('documento-tipos.destroy');

            Route::get('/cadastros/modelos-pdf/laboratorio', [DocumentoModeloLaboratorioController::class, 'index'])->name('documento-modelos.laboratorio');
            Route::post('/cadastros/modelos-pdf/laboratorio/upload', [DocumentoModeloLaboratorioController::class, 'upload'])->name('documento-modelos.laboratorio.upload');
            Route::post('/cadastros/modelos-pdf/laboratorio/novo-modelo', [DocumentoModeloLaboratorioController::class, 'storeNovo'])->name('documento-modelos.laboratorio.store-novo');
            Route::post('/cadastros/modelos-pdf/laboratorio/repor-esqueleto-global', [DocumentoModeloLaboratorioController::class, 'reporEsqueletoGlobal'])->name('documento-modelos.laboratorio.repor-global');
            Route::post('/cadastros/modelos-pdf/laboratorio/ocultar-catalogo', [DocumentoModeloLaboratorioController::class, 'ocultarCatalogo'])->name('documento-modelos.laboratorio.ocultar-catalogo');
            Route::delete('/cadastros/modelos-pdf/laboratorio/modelo/{modelo}', [DocumentoModeloLaboratorioController::class, 'destroy'])->name('documento-modelos.laboratorio.destroy');

            Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');

            Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
            Route::get('/relatorios/processos-por-status', [RelatorioController::class, 'processosPorStatus'])->name('relatorios.processos-status');
            Route::get('/relatorios/processos-por-periodo', [RelatorioController::class, 'processosPorPeriodo'])->name('relatorios.processos-periodo');
            Route::get('/relatorios/clientes-por-periodo', [RelatorioController::class, 'clientesPorPeriodo'])->name('relatorios.clientes-periodo');
            Route::get('/relatorios/export/processos.csv', [RelatorioController::class, 'exportProcessosCsv'])->name('relatorios.export.processos');
            Route::get('/relatorios/export/clientes.csv', [RelatorioController::class, 'exportClientesCsv'])->name('relatorios.export.clientes');

            Route::prefix('documento-modelos')->name('documento-modelos.')->group(function () {
                Route::get('/{modelo}/verificacao', [DocumentoModeloController::class, 'verificacao'])->name('verificacao');
                Route::post('/{modelo}/confirmar-mapeamento-upload', [DocumentoModeloController::class, 'confirmarMapeamentoUpload'])->name('confirmar-mapeamento-upload');
                Route::get('/{modelo}/editar', [DocumentoModeloController::class, 'edit'])->name('edit');
                Route::post('/{modelo}/duplicar', [DocumentoModeloController::class, 'duplicate'])->name('duplicate');
                Route::post('/{modelo}/restaurar-padrao', [DocumentoModeloController::class, 'restoreDefault'])->name('restore-default');
                Route::post('/{modelo}/sincronizar-ficheiro-padrao', [DocumentoModeloController::class, 'syncPadraoParaDisco'])->name('sync-padrao-disco');
                Route::patch('/{modelo}', [DocumentoModeloController::class, 'update'])->name('update');
            });
        });

        Route::middleware('master.admin')->group(function () {
            Route::get('/auditoria', [PlatformAuditoriaController::class, 'index'])->name('auditoria.index');
        });

        Route::post('/impersonate/{user}', [PlatformImpersonateController::class, 'start'])
            ->whereNumber('user')
            ->name('impersonate.start');

        Route::get('/usuarios', [PlatformUsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/criar', [PlatformUsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [PlatformUsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{user}/editar', [PlatformUsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::patch('/usuarios/{user}', [PlatformUsuarioController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{user}/password-reset', [PlatformUsuarioController::class, 'sendPasswordReset'])->name('usuarios.password-reset');

        Route::prefix('cadastros')->name('cadastros.')->group(function () {
            Route::get('/tipos-processo', [PlatformTipoProcessoController::class, 'index'])->name('tipos-processo.index');
            Route::get('/tipos-processo/criar', [PlatformTipoProcessoController::class, 'create'])->name('tipos-processo.create');
            Route::post('/tipos-processo', [PlatformTipoProcessoController::class, 'store'])->name('tipos-processo.store');
            Route::get('/tipos-processo/{tipo_processo}/editar', [PlatformTipoProcessoController::class, 'edit'])->name('tipos-processo.edit');
            Route::patch('/tipos-processo/{tipo_processo}', [PlatformTipoProcessoController::class, 'update'])->name('tipos-processo.update');
            Route::put('/tipos-processo/{tipo_processo}/regras', [PlatformTipoProcessoController::class, 'updateRegras'])->name('tipos-processo.update-regras');
            Route::post('/tipos-processo/bulk', [PlatformTipoProcessoController::class, 'bulk'])->name('tipos-processo.bulk');

            Route::get('/tipos-servico', [PlatformTipoServicoController::class, 'index'])->name('tipos-servico.index');
            Route::get('/tipos-servico/criar', [PlatformTipoServicoController::class, 'create'])->name('tipos-servico.create');
            Route::post('/tipos-servico', [PlatformTipoServicoController::class, 'store'])->name('tipos-servico.store');
            Route::get('/tipos-servico/{tipo_servico}/editar', [PlatformTipoServicoController::class, 'edit'])->name('tipos-servico.edit');
            Route::patch('/tipos-servico/{tipo_servico}', [PlatformTipoServicoController::class, 'update'])->name('tipos-servico.update');

            Route::get('/checklist-documentos', [ChecklistDocumentosController::class, 'index'])->name('checklist-documentos.index');
            Route::get('/checklist-documentos/criar', [ChecklistDocumentosController::class, 'create'])->name('checklist-documentos.create');
            Route::post('/checklist-documentos', [ChecklistDocumentosController::class, 'store'])->name('checklist-documentos.store');
            Route::get('/checklist-documentos/{documento_tipo}/editar', [ChecklistDocumentosController::class, 'edit'])->whereNumber('documento_tipo')->name('checklist-documentos.edit');
            Route::patch('/checklist-documentos/{documento_tipo}', [ChecklistDocumentosController::class, 'update'])->whereNumber('documento_tipo')->name('checklist-documentos.update');
            Route::delete('/checklist-documentos/{documento_tipo}', [ChecklistDocumentosController::class, 'destroy'])->whereNumber('documento_tipo')->name('checklist-documentos.destroy');

            Route::get('/anexo-tipos', [PlatformAnexoTipoController::class, 'index'])->name('anexo-tipos.index');
            Route::get('/anexo-tipos/criar', [PlatformAnexoTipoController::class, 'create'])->name('anexo-tipos.create');
            Route::post('/anexo-tipos', [PlatformAnexoTipoController::class, 'store'])->name('anexo-tipos.store');
            Route::get('/anexo-tipos/{anexo_tipo}/editar', [PlatformAnexoTipoController::class, 'edit'])->name('anexo-tipos.edit');
            Route::patch('/anexo-tipos/{anexo_tipo}', [PlatformAnexoTipoController::class, 'update'])->name('anexo-tipos.update');

            Route::get('/documentos-automatizados/laboratorio', [PlatformDocumentoModeloGlobalLaboratorioController::class, 'index'])->name('documentos-automatizados.laboratorio');
            Route::post('/documentos-automatizados/laboratorio/upload', [PlatformDocumentoModeloGlobalLaboratorioController::class, 'upload'])->name('documentos-automatizados.laboratorio.upload');
            Route::post('/documentos-automatizados/laboratorio/novo-modelo', [PlatformDocumentoModeloGlobalLaboratorioController::class, 'storeNovo'])->name('documentos-automatizados.laboratorio.store-novo');
            Route::get('/documentos-automatizados/preview', DocumentoModeloGlobalPreviewController::class)->name('documentos-automatizados.preview');

            Route::get('/documentos-automatizados', [PlatformDocumentoModeloGlobalController::class, 'index'])->name('documentos-automatizados.index');
            Route::get('/documentos-automatizados/criar', [PlatformDocumentoModeloGlobalController::class, 'create'])->name('documentos-automatizados.create');
            Route::post('/documentos-automatizados', [PlatformDocumentoModeloGlobalController::class, 'store'])->name('documentos-automatizados.store');
            Route::get('/documentos-automatizados/{documento_modelo_global}/editar', [PlatformDocumentoModeloGlobalController::class, 'edit'])->name('documentos-automatizados.edit');
            Route::patch('/documentos-automatizados/{documento_modelo_global}', [PlatformDocumentoModeloGlobalController::class, 'update'])->name('documentos-automatizados.update');
            Route::delete('/documentos-automatizados/{documento_modelo_global}', [PlatformDocumentoModeloGlobalController::class, 'destroy'])->name('documentos-automatizados.destroy');
            Route::post('/documentos-automatizados/{documento_modelo_global}/propagar', [PlatformDocumentoModeloGlobalController::class, 'propagar'])->name('documentos-automatizados.propagar');
        });
    });

});

// Encerrar impersonate deve funcionar mesmo como usuário alvo,
// inclusive quando não passou pelo middleware `verified`.
Route::middleware('auth')->group(function () {
    Route::post('/platform/impersonate/stop', [PlatformImpersonateController::class, 'stop'])->name('platform.impersonate.stop');
});

Route::middleware(['auth', 'tenant.empresa', 'tenant.subscription', 'permission:usuarios.manage'])->group(function () {
    Route::get('/equipe', [EquipeController::class, 'index'])->name('equipe.index');
    Route::get('/equipe/registos/exportar', [EquipeController::class, 'exportLogs'])->name('equipe.logs.export');
    Route::get('/equipe/criar', [EquipeController::class, 'create'])->name('equipe.create');
    Route::post('/equipe', [EquipeController::class, 'store'])->name('equipe.store');
    Route::get('/equipe/{usuario}/editar', [EquipeController::class, 'edit'])->name('equipe.edit');
    Route::post('/equipe/{usuario}/redefinir-senha-email', [EquipeController::class, 'sendPasswordResetLink'])->name('equipe.password-reset');
    Route::patch('/equipe/{usuario}', [EquipeController::class, 'update'])->name('equipe.update');
    Route::delete('/equipe/{usuario}', [EquipeController::class, 'destroy'])->name('equipe.destroy');
});

Route::middleware(['auth', 'tenant.empresa', 'tenant.subscription'])->group(function () {
    Route::get('/tour', [TourController::class, 'index'])->name('tour.index');

    Route::middleware(['permission:financeiro.view', 'tenant.financeiro.billing'])->group(function () {
        Route::prefix('financeiro')->name('financeiro.')->group(function () {
            Route::get('/', [FinanceiroController::class, 'index'])->name('index');

            Route::get('/export/aulas.csv', [FinanceiroController::class, 'exportAulasCsv'])->name('export.aulas');
            Route::get('/export/admin-direto.csv', [FinanceiroController::class, 'exportAdminDiretoCsv'])->name('export.admin_direto');
            Route::get('/export/despesas.csv', [FinanceiroController::class, 'exportDespesasCsv'])->name('export.despesas');
            Route::get('/export/parcerias-b2b.csv', [FinanceiroController::class, 'exportParceriasCsv'])->name('export.parcerias');
            Route::get('/export/engenharia.csv', [FinanceiroController::class, 'exportEngenhariaCsv'])->name('export.engenharia');

            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/resumo', [FinanceiroController::class, 'apiResumo'])->name('resumo');
                Route::get('/grafico/caixa', [FinanceiroController::class, 'apiGraficoCaixa'])->name('grafico.caixa');
                Route::get('/grafico/servicos', [FinanceiroController::class, 'apiGraficoServicos'])->name('grafico.servicos');
                Route::get('/lista/{modulo}', [FinanceiroController::class, 'apiLista'])->name('lista');
                Route::get('/notas', [FinanceiroController::class, 'apiNotas'])->name('notas');
            });

            Route::middleware(['permission:financeiro.manage'])->group(function () {
                Route::post('/aulas', [FinanceiroController::class, 'storeAula'])->name('store.aula');
                Route::patch('/aulas/{lancamento}', [FinanceiroController::class, 'updateAula'])->name('update.aula');
                Route::delete('/aulas/{lancamento}', [FinanceiroController::class, 'destroyAula'])->name('destroy.aula');

                Route::post('/admin-direto', [FinanceiroController::class, 'storeAdminDireto'])->name('store.admin_direto');
                Route::patch('/admin-direto/{lancamento}', [FinanceiroController::class, 'updateAdminDireto'])->name('update.admin_direto');
                Route::delete('/admin-direto/{lancamento}', [FinanceiroController::class, 'destroyAdminDireto'])->name('destroy.admin_direto');

                Route::post('/despesas', [FinanceiroController::class, 'storeDespesa'])->name('store.despesa');
                Route::patch('/despesas/{lancamento}', [FinanceiroController::class, 'updateDespesa'])->name('update.despesa');
                Route::delete('/despesas/{lancamento}', [FinanceiroController::class, 'destroyDespesa'])->name('destroy.despesa');

                Route::post('/parcerias', [FinanceiroController::class, 'storeLoteParceria'])->name('store.parceria');
                Route::patch('/parcerias/{lote}', [FinanceiroController::class, 'updateLoteParceria'])->name('update.parceria');
                Route::delete('/parcerias/{lote}', [FinanceiroController::class, 'destroyLoteParceria'])->name('destroy.parceria');
                Route::post('/parcerias/{lote}/itens', [FinanceiroController::class, 'storeItemLoteParceria'])->name('store.parceria.item');
                Route::patch('/parcerias/itens/{item}', [FinanceiroController::class, 'updateItemLoteParceria'])->name('update.parceria.item');
                Route::delete('/parcerias/itens/{item}', [FinanceiroController::class, 'destroyItemLoteParceria'])->name('destroy.parceria.item');

                Route::post('/engenharia', [FinanceiroController::class, 'storeLoteEngenharia'])->name('store.engenharia');
                Route::patch('/engenharia/{lote}', [FinanceiroController::class, 'updateLoteEngenharia'])->name('update.engenharia');
                Route::delete('/engenharia/{lote}', [FinanceiroController::class, 'destroyLoteEngenharia'])->name('destroy.engenharia');
                Route::post('/engenharia/{lote}/itens', [FinanceiroController::class, 'storeItemLoteEngenharia'])->name('store.engenharia.item');
                Route::patch('/engenharia/itens/{item}', [FinanceiroController::class, 'updateItemLoteEngenharia'])->name('update.engenharia.item');
                Route::delete('/engenharia/itens/{item}', [FinanceiroController::class, 'destroyItemLoteEngenharia'])->name('destroy.engenharia.item');

                Route::post('/notas/emitir', [FinanceiroController::class, 'emitirNota'])->name('notas.emitir');

                Route::post('/upload/admin-direto/{lancamento}', [FinanceiroController::class, 'uploadAdminDiretoComprovante'])->name('upload.admin_direto');
                Route::post('/upload/despesas/{lancamento}', [FinanceiroController::class, 'uploadDespesaNota'])->name('upload.despesa');
                Route::post('/upload/lote-parceria/{lote}', [FinanceiroController::class, 'uploadLoteParceriaComprovante'])->name('upload.lote_parceria');
                Route::post('/upload/lote-engenharia/{lote}', [FinanceiroController::class, 'uploadLoteEngenhariaComprovante'])->name('upload.lote_engenharia');
            });

            Route::middleware('signed')->group(function () {
                Route::get('/anexo/admin-direto/{lancamento}', [FinanceiroController::class, 'downloadAdminDiretoComprovante'])->name('anexo.admin_direto');
                Route::get('/anexo/despesas/{lancamento}', [FinanceiroController::class, 'downloadDespesaNota'])->name('anexo.despesa');
                Route::get('/anexo/lote-parceria/{lote}', [FinanceiroController::class, 'downloadLoteParceriaComprovante'])->name('anexo.lote_parceria');
                Route::get('/anexo/lote-engenharia/{lote}', [FinanceiroController::class, 'downloadLoteEngenhariaComprovante'])->name('anexo.lote_engenharia');
            });
        });
    });

    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/criar', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::post('/clientes/modal-store', [ClienteController::class, 'modalStore'])->name('clientes.modal-store');
    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::patch('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::post('/clientes/{cliente}/anexos', [ClienteController::class, 'storeAnexos'])->name('clientes.anexos.store');
    Route::delete('/f/c/{anexo}', [ClienteController::class, 'destroyAnexo'])->name('clientes.anexos.destroy');
    Route::middleware('signed')->group(function () {
        Route::get('/f/c/{anexo}/v', [ClienteAnexoFileController::class, 'inline'])->name('clientes.anexos.inline');
        Route::get('/f/c/{anexo}/d', [ClienteAnexoFileController::class, 'download'])->name('clientes.anexos.download');
        Route::get('/f/c/{anexo}/p', [ClienteAnexoFileController::class, 'print'])->name('clientes.anexos.print');
    });

    Route::get('/embarcacoes', [EmbarcacaoController::class, 'index'])->name('embarcacoes.index');
    Route::get('/embarcacoes/criar', [EmbarcacaoController::class, 'create'])->name('embarcacoes.create');
    Route::post('/embarcacoes', [EmbarcacaoController::class, 'store'])->name('embarcacoes.store');
    Route::get('/embarcacoes/{embarcacao}/editar', [EmbarcacaoController::class, 'edit'])->name('embarcacoes.edit');
    Route::patch('/embarcacoes/{embarcacao}', [EmbarcacaoController::class, 'update'])->name('embarcacoes.update');
    Route::get('/embarcacoes/{embarcacao}', [EmbarcacaoController::class, 'show'])->name('embarcacoes.show');
    Route::post('/embarcacoes/{embarcacao}/fotos-cadastro', [EmbarcacaoController::class, 'storeFotosCadastro'])->name('embarcacoes.fotos-cadastro.store');
    Route::post('/embarcacoes/{embarcacao}/anexos', [EmbarcacaoController::class, 'storeAnexos'])->name('embarcacoes.anexos.store');
    Route::delete('/f/e/{anexo}', [EmbarcacaoController::class, 'destroyAnexo'])->name('embarcacoes.anexos.destroy');
    Route::middleware('signed')->group(function () {
        Route::get('/f/e/{anexo}/v', [EmbarcacaoAnexoFileController::class, 'inline'])->name('embarcacoes.anexos.inline');
        Route::get('/f/e/{anexo}/d', [EmbarcacaoAnexoFileController::class, 'download'])->name('embarcacoes.anexos.download');
        Route::get('/f/e/{anexo}/p', [EmbarcacaoAnexoFileController::class, 'print'])->name('embarcacoes.anexos.print');
    });

    Route::get('/habilitacoes', [HabilitacaoController::class, 'index'])->name('habilitacoes.index');
    Route::get('/habilitacoes/criar', [HabilitacaoController::class, 'create'])->name('habilitacoes.create');
    Route::post('/habilitacoes', [HabilitacaoController::class, 'store'])->name('habilitacoes.store');
    Route::get('/habilitacoes/{habilitacao}/editar', [HabilitacaoController::class, 'edit'])->name('habilitacoes.edit');
    Route::patch('/habilitacoes/{habilitacao}', [HabilitacaoController::class, 'update'])->name('habilitacoes.update');
    Route::get('/habilitacoes/{habilitacao}', [HabilitacaoController::class, 'show'])->name('habilitacoes.show');
    Route::post('/habilitacoes/{habilitacao}/anexos', [HabilitacaoController::class, 'storeAnexos'])->name('habilitacoes.anexos.store');
    Route::delete('/f/h/{anexo}', [HabilitacaoController::class, 'destroyAnexo'])->name('habilitacoes.anexos.destroy');
    Route::middleware('signed')->group(function () {
        Route::get('/f/h/{anexo}/v', [HabilitacaoAnexoFileController::class, 'inline'])->name('habilitacoes.anexos.inline');
        Route::get('/f/h/{anexo}/d', [HabilitacaoAnexoFileController::class, 'download'])->name('habilitacoes.anexos.download');
        Route::get('/f/h/{anexo}/p', [HabilitacaoAnexoFileController::class, 'print'])->name('habilitacoes.anexos.print');
    });

    Route::get('/processos/create', [ProcessoController::class, 'create'])->name('processos.create');
    Route::post('/processos', [ProcessoController::class, 'store'])->name('processos.store');
    Route::post('/processos/excluir-lote', [ProcessoController::class, 'destroyMany'])->name('processos.destroyMany');
    Route::post('/processos/alterar-status-lote', [ProcessoController::class, 'updateStatusMany'])->name('processos.updateStatusMany');
    Route::get('/processos/kanban', [ProcessoController::class, 'kanban'])->name('processos.kanban');
    Route::get('/processos', [ProcessoController::class, 'index'])->name('processos.index');
    Route::get('/processos/{processo}', [ProcessoController::class, 'show'])->name('processos.show');
    Route::delete('/processos/{processo}', [ProcessoController::class, 'destroy'])->name('processos.destroy');
    Route::patch('/processos/{processo}/observacoes', [ProcessoController::class, 'updateObservacoes'])->name('processos.observacoes.update');
    Route::patch('/processos/{processo}/protocolo-marinha', [ProcessoController::class, 'updateProtocoloMarinha'])->name('processos.protocolo-marinha.update');
    Route::patch('/processos/{processo}/prova-marinha', [ProcessoController::class, 'updateProvaMarinha'])->name('processos.prova-marinha.update');
    Route::get('/processos/{processo}/protocolo-marinha/anexo', [ProcessoController::class, 'downloadProtocoloMarinhaAnexo'])->name('processos.protocolo-marinha.anexo');
    Route::post('/processos/{processo}/post-its', [ProcessoController::class, 'storePostIt'])->name('processos.post-its.store');
    Route::patch('/processos/{processo}/post-its/{postIt}', [ProcessoController::class, 'updatePostIt'])->name('processos.post-its.update');
    Route::delete('/processos/{processo}/post-its/{postIt}', [ProcessoController::class, 'destroyPostIt'])->name('processos.post-its.destroy');
    Route::patch('/processos/{processo}/status', [ProcessoController::class, 'updateStatus'])->name('processos.status');
    Route::post('/processos/{processo}/documentos/{documento}/anexos', [ProcessoController::class, 'storeAnexos'])->name('processos.documentos.anexos.store');
    Route::middleware('signed')->group(function () {
        Route::get('/f/p/{anexo}/v', [ProcessoDocumentoAnexoFileController::class, 'inline'])->name('processos.documentos.anexos.inline');
    });
    Route::delete('/f/p/{anexo}', [ProcessoController::class, 'destroyAnexo'])->name('processos.documentos.anexos.destroy');
    Route::patch('/processos/{processo}/documentos/{documento}', [ProcessoController::class, 'updateDocumento'])->name('processos.documentos.update');

    Route::middleware(['permission:clientes.manage'])->group(function () {
        Route::get('/documento-modelos', function () {
            return redirect()->route('dashboard');
        })->name('documento-modelos.index');
        Route::get('/documento-modelos/{modelo}/verificacao', [DocumentoModeloController::class, 'verificacao'])
            ->name('documento-modelos.verificacao');
        Route::post('/documento-modelos', [DocumentoModeloController::class, 'store'])->name('documento-modelos.store');
        Route::get('/documento-modelos/{modelo}/editar', [DocumentoModeloController::class, 'edit'])->name('documento-modelos.edit');
        Route::post('/documento-modelos/{modelo}/duplicar', [DocumentoModeloController::class, 'duplicate'])->name('documento-modelos.duplicate');
        Route::post('/documento-modelos/{modelo}/restaurar-padrao', [DocumentoModeloController::class, 'restoreDefault'])->name('documento-modelos.restore-default');
        Route::post('/documento-modelos/{modelo}/sincronizar-ficheiro-padrao', [DocumentoModeloController::class, 'syncPadraoParaDisco'])->name('documento-modelos.sync-padrao-disco');
        Route::post('/documento-modelos/{modelo}/sincronizar-ficheiro-para-bd', [DocumentoModeloController::class, 'syncDiscoParaBd'])->name('documento-modelos.sync-disco-bd');
        Route::post('/documento-modelos/{modelo}/confirmar-mapeamento-upload', [DocumentoModeloController::class, 'confirmarMapeamentoUpload'])->name('documento-modelos.confirmar-mapeamento-upload');
        Route::patch('/documento-modelos/{modelo}', [DocumentoModeloController::class, 'update'])->name('documento-modelos.update');
    });

    // Render de um modelo para um cliente (ex.: anexo-2g)
    Route::get('/clientes/{cliente}/documento-modelos/{slug}', [DocumentoModeloRenderController::class, 'render'])
        ->name('clientes.documento-modelos.render');

    // Aulas náuticas (rotas estáticas antes de /aulas/{aula})
    Route::middleware(['permission:aulas.view'])->group(function () {
        Route::get('/aulas', [AulaNauticaController::class, 'index'])->name('aulas.index');
        Route::get('/aulas/atestados', [AulaAtestadoController::class, 'index'])->name('aulas.atestados.index');
        Route::get('/aulas/comunicados', [AulaComunicadoController::class, 'index'])->name('aulas.comunicados.index');

        Route::middleware(['permission:aulas.manage'])->group(function () {
            Route::get('/aulas/escola/instrutores', [AulaEscolaController::class, 'instrutores'])->name('aulas.escola.instrutores');
            Route::get('/aulas/escola', [AulaEscolaController::class, 'edit'])->name('aulas.escola.edit');
            Route::put('/aulas/escola', [AulaEscolaController::class, 'update'])->name('aulas.escola.update');
            Route::post('/aulas/escola/capitanias', [AulaEscolaController::class, 'storeCapitania'])->name('aulas.escola.capitanias.store');
            Route::patch('/aulas/escola/capitanias/{capitania}', [AulaEscolaController::class, 'updateCapitania'])->name('aulas.escola.capitanias.update');
            Route::delete('/aulas/escola/capitanias/{capitania}', [AulaEscolaController::class, 'destroyCapitania'])->name('aulas.escola.capitanias.destroy');
            Route::post('/aulas/escola/instrutores', [EscolaInstrutorController::class, 'store'])->name('aulas.escola.instrutores.store');
            Route::patch('/aulas/escola/instrutores/{escola_instrutor}', [EscolaInstrutorController::class, 'update'])->name('aulas.escola.instrutores.update');
            Route::delete('/aulas/escola/instrutores/{escola_instrutor}', [EscolaInstrutorController::class, 'destroy'])->name('aulas.escola.instrutores.destroy');
            Route::post('/aulas/atestados/duracoes', [AulaAtestadoController::class, 'storeDurations'])->name('aulas.atestados.duracoes.store');
            Route::patch('/aulas/{aula}/comunicado-enviado', [AulaComunicadoController::class, 'markEnviado'])->name('aulas.comunicado-enviado');
        });

        Route::get('/aulas/{aula}', [AulaNauticaController::class, 'show'])->name('aulas.show');
        Route::get('/aulas/{aula}/pdf/comunicado', [AulaNauticaPdfController::class, 'comunicado'])->name('aulas.pdf.comunicado');
        Route::get('/aulas/{aula}/pdf/ara/{aluno}', [AulaNauticaPdfController::class, 'ara'])->name('aulas.pdf.ara');
        Route::get('/aulas/{aula}/pdf/mtm', [AulaNauticaPdfController::class, 'mta'])->name('aulas.pdf.mta');
        Route::get('/aulas/{aula}/documento-automatico/{indice}', [AulaNauticaController::class, 'downloadDocumentoAutomatico'])
            ->whereNumber('indice')
            ->name('aulas.documento-automatico.download');
    });
    Route::middleware(['permission:aulas.manage'])->group(function () {
        Route::get('/aulas/nova', [AulaNauticaController::class, 'create'])->name('aulas.create');
        Route::post('/aulas', [AulaNauticaController::class, 'store'])->name('aulas.store');
        Route::get('/aulas/{aula}/editar', [AulaNauticaController::class, 'edit'])->name('aulas.edit');
        Route::patch('/aulas/{aula}', [AulaNauticaController::class, 'update'])->name('aulas.update');

        Route::get('/alunos/buscar-cpf', [AlunoAjaxController::class, 'buscarCpf'])->name('alunos.buscar-cpf');
        Route::get('/alunos/buscar-escola-instrutor-cpf', [AlunoAjaxController::class, 'buscarEscolaInstrutorCpf'])->name('alunos.buscar-escola-instrutor-cpf');
        Route::post('/alunos/modal-store', [AlunoAjaxController::class, 'modalStore'])->name('alunos.modal-store');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware(['permission:empresa.manage'])->group(function () {
            Route::get('/empresa', [EmpresaSettingsController::class, 'edit'])->name('empresa.edit');
            Route::patch('/empresa', [EmpresaSettingsController::class, 'update'])->name('empresa.update');
            Route::prefix('empresa')->name('empresa.')->group(function () {
                Route::resource('compromissos', EmpresaCompromissoController::class)->except(['show']);
            });
        });
        Route::get('/empresa/logo', [EmpresaSettingsController::class, 'logo'])->name('empresa.logo');
        Route::get('/auditoria', [AuditoriaController::class, 'index'])
            ->middleware(['permission:auditoria.view'])
            ->name('auditoria.index');
    });
});

require __DIR__.'/auth.php';
