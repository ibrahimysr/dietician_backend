<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Auth\AuthenticationException;

class CustomAuthenticate extends Middleware
{
  
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }

  
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            throw new AuthenticationException(
                'Geçersiz veya eksik token',
                $guards,
                null
            );
        }

-        parent::unauthenticated($request, $guards);
    }
}