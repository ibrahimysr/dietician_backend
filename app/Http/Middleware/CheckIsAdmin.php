<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckIsAdmin
{
   
    public function handle(Request $request, Closure $next): Response
    {
        
        if (!Auth::check() || Auth::user()->role !== 'admin') {
             return response()->json(['success' => false, 'message' => 'Yetkisiz Erişim.'], 403);
        }

        return $next($request);
    }
}