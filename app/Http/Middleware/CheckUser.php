<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user() == null || auth()->user()->id < 1) {
            return response()->json([
                'status'    =>  false,
                'error'     =>  'ERROR_NOT_FOUND',
                'message'   =>  'Login terlebih dahulu',
            ], 422);
        }   
        return $next($request);
    }
}
