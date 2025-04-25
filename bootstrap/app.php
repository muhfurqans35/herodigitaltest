<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use App\Http\Middleware\SubscriptionLimitMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias(
            [
                'global.role' => \App\Http\Middleware\CheckGlobalRole::class,
                'subscription.limits' => SubscriptionLimitMiddleware::class,
                'tenant.role' => \App\Http\Middleware\CheckTenantRole::class,
                'tenant.or.global' => \App\Http\Middleware\CheckTenantOrGlobalRole::class,
            ]

        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
