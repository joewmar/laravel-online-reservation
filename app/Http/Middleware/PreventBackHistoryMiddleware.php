<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistoryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // To prevent Back HIstory When Logged in
        $response = $next($request);
        return $response->header('Cache-Control', 'nocache,no-store,max-age=0;must-revalidate')
                        ->header('Prgram', 'no-cache')
                        ->header('Expire', 'Sun, 02 1990 00:00:00 GMT');
    }
}
