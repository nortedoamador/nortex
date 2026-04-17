<?php

use App\Http\Middleware\CheckPlatformMaintenance;
use App\Http\Middleware\EnsureAnyPermission;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureMasterAdmin;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureTenantEmpresa;
use App\Http\Middleware\EnsureTenantFinanceiroBillingAccess;
use App\Http\Middleware\EnsureTenantSubscription;
use App\Http\Middleware\LogPlatformHttpMutation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.empresa' => EnsureTenantEmpresa::class,
            'tenant.subscription' => EnsureTenantSubscription::class,
            'tenant.financeiro.billing' => EnsureTenantFinanceiroBillingAccess::class,
            'permission' => EnsurePermission::class,
            'permission.any' => EnsureAnyPermission::class,
            'platform.admin' => EnsurePlatformAdmin::class,
            'master.admin' => EnsureMasterAdmin::class,
            'platform.audit' => LogPlatformHttpMutation::class,
        ]);
        $middleware->appendToGroup('web', [
            CheckPlatformMaintenance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
