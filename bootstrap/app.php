<?php

use App\Http\Middleware\IsAdmin;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AlreadyLoggedIn;
use App\Http\Middleware\EmailIsVerified;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'verified.otp' => EmailIsVerified::class,
                'logged.in' => AlreadyLoggedIn::class,
                    'admin' => IsAdmin::class,


        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
