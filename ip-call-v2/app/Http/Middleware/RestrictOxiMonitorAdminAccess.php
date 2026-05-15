<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictOxiMonitorAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.oximonitor_can_access_admin') && ! Auth::check()) {
            return redirect('/login');
        }

        if (
            Auth::check()
            && Auth::user()->username === 'oximonitor'
            && ! config('app.oximonitor_can_access_admin')
        ) {
            return redirect('/oximonitor');
        }

        return $next($request);
    }
}
