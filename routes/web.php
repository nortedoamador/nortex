<?php

use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\DocumentoModeloLaboratorioController;
use App\Http\Controllers\Admin\DocumentoTipoAdminController;
use App\Http\Controllers\Admin\EmpresaSettingsController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TipoProcessoAdminController;
use App\Http\Controllers\Api\CnhExtractController;
use App\Http\Controllers\ClienteAnexoFileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmbarcacaoAnexoFileController;
use App\Http\Controllers\HabilitacaoAnexoFileController;
use App\Http\Controllers\DocumentoModeloController;
use App\Http\Controllers\DocumentoModeloRenderController;
use App\Http\Controllers\EmbarcacaoController;
use App\Http\Controllers\HabilitacaoController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\Platform\EmpresaController as PlatformEmpresaController;
use App\Http\Controllers\Platform\TipoProcessoController as PlatformTipoProcessoController;
use App\Http\Controllers\Platform\TipoServicoController as PlatformTipoServicoController;
use App\Http\Controllers\Platform\AnexoTipoController as PlatformAnexoTipoController;
use App\Http\Controllers\Platform\AuditoriaController as PlatformAuditoriaController;
use App\Http\Controllers\Platform\ImpersonateController as PlatformImpersonateController;
use App\Http\Controllers\Platform\UsuarioController as PlatformUsuarioController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\EmpresaAdminUserController as PlatformEmpresaAdminUserController;
use App\Http\Controllers\ProcessoController;
use App\Http\Controllers\ProfileController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

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

    if ($auth->is_platform_admin) {
        $empresa = request()->route('empresa');
        $empresaId = null;
        if ($empresa instanceof \App\Models\Empresa) {
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
        if ($user->is_platform_admin && ! $user->empresa_id) {
            return redirect()->route('platform.empresas.index');
        }

        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', [ProcessoController::class, 'dashboard'])
    ->middleware(['auth', 'verified', 'tenant.empresa'])
    ->name('dashboard');

Route::get('/clientes/{cliente}/embarcacoes-options', [ClienteController::class, 'embarcacoesOptions'])
    ->middleware(['auth', 'verified', 'tenant.empresa'])
    ->name('clientes.embarcacoes.options');

Route::get('/clientes/{cliente}/habilitacoes-options', [ClienteController::class, 'habilitacoesOptions'])
    ->middleware(['auth', 'verified', 'tenant.empresa'])
    ->name('clientes.habilitacoes.options');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('platform')->name('platform.')->middleware('platform.admin')->group(function () {
        Route::get('/', PlatformDashboardController::class)->name('dashboard');
        Route::get('/empresas', [PlatformEmpresaController::class, 'index'])->name('empresas.index');
        Route::get('/empresas/criar', [PlatformEmpresaController::class, 'create'])->name('empresas.create');
        Route::post('/empresas', [PlatformEmpresaController::class, 'store'])->name('empresas.store');
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

        Route::get('/auditoria', [PlatformAuditoriaController::class, 'index'])->name('auditoria.index');

        Route::post('/impersonate/{user}', [PlatformImpersonateController::class, 'start'])->name('impersonate.start');

        Route::get('/usuarios', [PlatformUsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/{user}/editar', [PlatformUsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::patch('/usuarios/{user}', [PlatformUsuarioController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{user}/password-reset', [PlatformUsuarioController::class, 'sendPasswordReset'])->name('usuarios.password-reset');

        Route::prefix('cadastros')->name('cadastros.')->group(function () {
            Route::get('/tipos-processo', [PlatformTipoProcessoController::class, 'index'])->name('tipos-processo.index');
            Route::get('/tipos-processo/criar', [PlatformTipoProcessoController::class, 'create'])->name('tipos-processo.create');
            Route::post('/tipos-processo', [PlatformTipoProcessoController::class, 'store'])->name('tipos-processo.store');
            Route::get('/tipos-processo/{tipo_processo}/editar', [PlatformTipoProcessoController::class, 'edit'])->name('tipos-processo.edit');
            Route::patch('/tipos-processo/{tipo_processo}', [PlatformTipoProcessoController::class, 'update'])->name('tipos-processo.update');

            Route::get('/tipos-servico', [PlatformTipoServicoController::class, 'index'])->name('tipos-servico.index');
            Route::get('/tipos-servico/criar', [PlatformTipoServicoController::class, 'create'])->name('tipos-servico.create');
            Route::post('/tipos-servico', [PlatformTipoServicoController::class, 'store'])->name('tipos-servico.store');
            Route::get('/tipos-servico/{tipo_servico}/editar', [PlatformTipoServicoController::class, 'edit'])->name('tipos-servico.edit');
            Route::patch('/tipos-servico/{tipo_servico}', [PlatformTipoServicoController::class, 'update'])->name('tipos-servico.update');

            Route::get('/anexo-tipos', [PlatformAnexoTipoController::class, 'index'])->name('anexo-tipos.index');
            Route::get('/anexo-tipos/criar', [PlatformAnexoTipoController::class, 'create'])->name('anexo-tipos.create');
            Route::post('/anexo-tipos', [PlatformAnexoTipoController::class, 'store'])->name('anexo-tipos.store');
            Route::get('/anexo-tipos/{anexo_tipo}/editar', [PlatformAnexoTipoController::class, 'edit'])->name('anexo-tipos.edit');
            Route::patch('/anexo-tipos/{anexo_tipo}', [PlatformAnexoTipoController::class, 'update'])->name('anexo-tipos.update');
        });
    });

    // Encerrar impersonate deve funcionar mesmo como usuário alvo (sem ser platform admin)
    Route::post('/platform/impersonate/stop', [PlatformImpersonateController::class, 'stop'])->name('platform.impersonate.stop');
});

Route::middleware(['auth', 'tenant.empresa', 'permission:usuarios.manage'])->group(function () {
    Route::get('/equipe', [EquipeController::class, 'index'])->name('equipe.index');
    Route::get('/equipe/registos/exportar', [EquipeController::class, 'exportLogs'])->name('equipe.logs.export');
    Route::get('/equipe/criar', [EquipeController::class, 'create'])->name('equipe.create');
    Route::post('/equipe', [EquipeController::class, 'store'])->name('equipe.store');
    Route::get('/equipe/{usuario}/editar', [EquipeController::class, 'edit'])->name('equipe.edit');
    Route::post('/equipe/{usuario}/redefinir-senha-email', [EquipeController::class, 'sendPasswordResetLink'])->name('equipe.password-reset');
    Route::patch('/equipe/{usuario}', [EquipeController::class, 'update'])->name('equipe.update');
    Route::delete('/equipe/{usuario}', [EquipeController::class, 'destroy'])->name('equipe.destroy');
});

Route::middleware(['auth', 'tenant.empresa'])->group(function () {
    Route::post('/api/cnh/extract', CnhExtractController::class)->name('api.cnh.extract');

    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/criar', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::patch('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::post('/clientes/{cliente}/anexos', [ClienteController::class, 'storeAnexos'])->name('clientes.anexos.store');
    Route::delete('/clientes/{cliente}/anexos/{anexo}', [ClienteController::class, 'destroyAnexo'])->name('clientes.anexos.destroy');
    Route::get('/clientes/{cliente}/anexos/{anexo}/inline', [ClienteAnexoFileController::class, 'inline'])->name('clientes.anexos.inline');
    Route::get('/clientes/{cliente}/anexos/{anexo}/download', [ClienteAnexoFileController::class, 'download'])->name('clientes.anexos.download');
    Route::get('/clientes/{cliente}/anexos/{anexo}/print', [ClienteAnexoFileController::class, 'print'])->name('clientes.anexos.print');

    Route::get('/embarcacoes', [EmbarcacaoController::class, 'index'])->name('embarcacoes.index');
    Route::get('/embarcacoes/criar', [EmbarcacaoController::class, 'create'])->name('embarcacoes.create');
    Route::post('/embarcacoes', [EmbarcacaoController::class, 'store'])->name('embarcacoes.store');
    Route::get('/embarcacoes/{embarcacao}/editar', [EmbarcacaoController::class, 'edit'])->name('embarcacoes.edit');
    Route::patch('/embarcacoes/{embarcacao}', [EmbarcacaoController::class, 'update'])->name('embarcacoes.update');
    Route::get('/embarcacoes/{embarcacao}', [EmbarcacaoController::class, 'show'])->name('embarcacoes.show');
    Route::post('/embarcacoes/{embarcacao}/fotos-cadastro', [EmbarcacaoController::class, 'storeFotosCadastro'])->name('embarcacoes.fotos-cadastro.store');
    Route::post('/embarcacoes/{embarcacao}/anexos', [EmbarcacaoController::class, 'storeAnexos'])->name('embarcacoes.anexos.store');
    Route::delete('/embarcacoes/{embarcacao}/anexos/{anexo}', [EmbarcacaoController::class, 'destroyAnexo'])->name('embarcacoes.anexos.destroy');
    Route::get('/embarcacoes/{embarcacao}/anexos/{anexo}/inline', [EmbarcacaoAnexoFileController::class, 'inline'])->name('embarcacoes.anexos.inline');
    Route::get('/embarcacoes/{embarcacao}/anexos/{anexo}/download', [EmbarcacaoAnexoFileController::class, 'download'])->name('embarcacoes.anexos.download');
    Route::get('/embarcacoes/{embarcacao}/anexos/{anexo}/print', [EmbarcacaoAnexoFileController::class, 'print'])->name('embarcacoes.anexos.print');

    Route::get('/habilitacoes', [HabilitacaoController::class, 'index'])->name('habilitacoes.index');
    Route::get('/habilitacoes/criar', [HabilitacaoController::class, 'create'])->name('habilitacoes.create');
    Route::post('/habilitacoes', [HabilitacaoController::class, 'store'])->name('habilitacoes.store');
    Route::get('/habilitacoes/{habilitacao}/editar', [HabilitacaoController::class, 'edit'])->name('habilitacoes.edit');
    Route::patch('/habilitacoes/{habilitacao}', [HabilitacaoController::class, 'update'])->name('habilitacoes.update');
    Route::get('/habilitacoes/{habilitacao}', [HabilitacaoController::class, 'show'])->name('habilitacoes.show');
    Route::post('/habilitacoes/{habilitacao}/anexos', [HabilitacaoController::class, 'storeAnexos'])->name('habilitacoes.anexos.store');
    Route::delete('/habilitacoes/{habilitacao}/anexos/{anexo}', [HabilitacaoController::class, 'destroyAnexo'])->name('habilitacoes.anexos.destroy');
    Route::get('/habilitacoes/{habilitacao}/anexos/{anexo}/inline', [HabilitacaoAnexoFileController::class, 'inline'])->name('habilitacoes.anexos.inline');
    Route::get('/habilitacoes/{habilitacao}/anexos/{anexo}/download', [HabilitacaoAnexoFileController::class, 'download'])->name('habilitacoes.anexos.download');
    Route::get('/habilitacoes/{habilitacao}/anexos/{anexo}/print', [HabilitacaoAnexoFileController::class, 'print'])->name('habilitacoes.anexos.print');

    Route::get('/processos/create', [ProcessoController::class, 'create'])->name('processos.create');
    Route::post('/processos', [ProcessoController::class, 'store'])->name('processos.store');
    Route::post('/processos/excluir-lote', [ProcessoController::class, 'destroyMany'])->name('processos.destroyMany');
    Route::post('/processos/alterar-status-lote', [ProcessoController::class, 'updateStatusMany'])->name('processos.updateStatusMany');
    Route::get('/processos/kanban', [ProcessoController::class, 'kanban'])->name('processos.kanban');
    Route::get('/processos', [ProcessoController::class, 'index'])->name('processos.index');
    Route::get('/processos/{processo}', [ProcessoController::class, 'show'])->name('processos.show');
    Route::delete('/processos/{processo}', [ProcessoController::class, 'destroy'])->name('processos.destroy');
    Route::patch('/processos/{processo}/observacoes', [ProcessoController::class, 'updateObservacoes'])->name('processos.observacoes.update');
    Route::post('/processos/{processo}/post-its', [ProcessoController::class, 'storePostIt'])->name('processos.post-its.store');
    Route::patch('/processos/{processo}/post-its/{postIt}', [ProcessoController::class, 'updatePostIt'])->name('processos.post-its.update');
    Route::delete('/processos/{processo}/post-its/{postIt}', [ProcessoController::class, 'destroyPostIt'])->name('processos.post-its.destroy');
    Route::patch('/processos/{processo}/status', [ProcessoController::class, 'updateStatus'])->name('processos.status');
    Route::post('/processos/{processo}/documentos/{documento}/anexos', [ProcessoController::class, 'storeAnexos'])->name('processos.documentos.anexos.store');
    Route::delete('/processos/{processo}/documentos/{documento}/anexos/{anexo}', [ProcessoController::class, 'destroyAnexo'])->name('processos.documentos.anexos.destroy');
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
        Route::post('/documento-modelos/{modelo}/confirmar-mapeamento-upload', [DocumentoModeloController::class, 'confirmarMapeamentoUpload'])->name('documento-modelos.confirmar-mapeamento-upload');
        Route::patch('/documento-modelos/{modelo}', [DocumentoModeloController::class, 'update'])->name('documento-modelos.update');
    });

    // Render de um modelo para um cliente (ex.: anexo-2g)
    Route::get('/clientes/{cliente}/documento-modelos/{slug}', [DocumentoModeloRenderController::class, 'render'])
        ->name('clientes.documento-modelos.render');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware(['permission:empresa.manage'])->group(function () {
            Route::get('/empresa', [EmpresaSettingsController::class, 'edit'])->name('empresa.edit');
            Route::patch('/empresa', [EmpresaSettingsController::class, 'update'])->name('empresa.update');
        });
    });
});

require __DIR__.'/auth.php';
