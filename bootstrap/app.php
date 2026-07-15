<?php

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
        $middleware->validateCsrfTokens(except: [
            'payments/razorpay/webhook',
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request): string => $request->is('team*') ? '/team/login' : '/login',
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
