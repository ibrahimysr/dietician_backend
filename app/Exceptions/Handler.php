<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

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

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Geçersiz veya eksik token',
                    'data' => null,
                ], 401);
            }

            if ($e instanceof RouteNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz istek: Yetkisiz erişim',
                    'data' => null,
                ], 401);
            }

            // Diğer genel hatalar için 500
            return response()->json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }

        return parent::render($request, $e);
    }
}