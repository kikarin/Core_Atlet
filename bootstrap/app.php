<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\InjectUserPermissions;
use App\Http\Middleware\ApiResponseMiddleware;
use App\Http\Middleware\CheckProgramLatihanPermission;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            InjectUserPermissions::class,
            // EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->api(append: [
            HandleCors::class,
            ApiResponseMiddleware::class,
        ]);

        // Register custom middleware
        $middleware->alias([
            'program.latihan.permission' => CheckProgramLatihanPermission::class,
        ]);

        // Sanctum middleware untuk stateful API (Remove)
        $middleware->statefulApi([
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // CSRF token validation exceptions
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
