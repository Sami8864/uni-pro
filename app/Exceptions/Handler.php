<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            if ($exception->getMessage() === 'Unauthenticated.') {
                return response()->json(['error' => 'Token revoked'], 401);
            }
        }

        if ($exception instanceof \Laravel\Passport\Exceptions\OAuthServerException) {
            if ($exception->getMessage() === 'Unauthenticated.') {
                return response()->json(['error' => 'Token expired'], 401);
            }
        }

        return parent::unauthenticated($request, $exception);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpException) {
            if ($exception->getStatusCode() == 500) {
                return response()->json(['error' => 'Internal Server Error'], 500);
            }
        }

        return parent::render($request, $exception);
    }

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
