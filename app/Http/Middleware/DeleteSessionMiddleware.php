<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        if(!($request->routeIs('user.reservation.edit.*'))) session()->forget('erinfo');
        if(!($request->routeIs('system.reservation.edit.*'))) session()->forget('esrinfo');
        if(!($request->is('register/*'))) session()->forget('uinfo');
        if(!($request->routeIs('profile.*'))) session()->forget('upuinfo');
        
        if(!($request->is('system/reservation/create/*'))) {
            $i = session('nwrinfo');
            if(isset($i['vid'])){
                $path = decrypt($i['vid']);
                Storage::delete('private/'.$path);
            }
            session()->forget('nwrinfo');
        }
        return $next($request);
    }
}
