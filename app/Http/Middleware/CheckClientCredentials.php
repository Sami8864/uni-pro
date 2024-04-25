<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class CheckClientCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Attempt to authenticate the user based on the access token
            Auth::guard('api')->authenticate();
        } catch (AuthenticationException $e) {
            // Invalid or missing access token
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
