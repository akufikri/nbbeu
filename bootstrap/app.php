<?php

use App\Http\Middleware\EnsureWebUserIsMember;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureWebUserIsMember::class,
        ]);

        // Laravel's default priority list hoists Authenticate ('auth') ahead of
        // any custom middleware regardless of registration order, so without
        // this, route-level 'auth:web' would run BEFORE this one and still see
        // a stale non-member session as logged in.
        $middleware->prependToPriorityList(
            before: AuthenticatesRequests::class,
            prepend: EnsureWebUserIsMember::class,
        );

        $middleware->validateCsrfTokens(except: [
            'webhooks/toyyibpay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
