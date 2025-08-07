<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Inertia\Inertia;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    protected $levels     = [];
    protected $dontReport = [];
    protected $dontFlash  = ['current_password', 'password', 'password_confirmation'];

    public function register()
    {
        //
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return parent::render($request, $exception);
        }

        if (
            $exception instanceof AuthorizationException || ($exception instanceof HttpException && $exception->getStatusCode() === 403)
        ) {
            return Inertia::render('errors/Error403')->toResponse($request)->setStatusCode(403);
        }

        return parent::render($request, $exception);
    }
}
