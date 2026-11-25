<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsurePro;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Ordre important : les routes spécifiques avant les "attrape-tout" de web.php.
            Route::middleware('web')->group(base_path('routes/auth.php'));
            Route::middleware(['web', 'auth', 'admin'])->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
            Route::middleware('web')->group(base_path('routes/immobilier.php'));
            Route::middleware('web')->group(base_path('routes/legacy.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'pro' => EnsurePro::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('immobilier*') ? route('immo.login') : route('login'));
        $middleware->redirectUsersTo(fn () => route('home'));

        // Les compteurs de clics (sendBeacon) ne transportent pas toujours le jeton.
        $middleware->validateCsrfTokens(except: ['stats/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié. Connectez-vous pour continuer.'], 401);
            }
        });
    })->create();
