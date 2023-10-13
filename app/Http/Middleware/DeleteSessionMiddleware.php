<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!($request->routeIs('reservation.*'))) session()->forget('rinfo');
        if(!($request->is('create/reservation/create/*'))) session()->forget('nwrinfo');
        return $next($request);
    }
}
