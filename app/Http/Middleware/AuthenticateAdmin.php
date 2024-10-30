<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{

    public function handle($request, Closure $next, $guard = 'admin')
    {
        if (!auth()->guard($guard)->check()) {
            return redirect()->route('admin.login.form');
        }
        return $next($request);
    }

}
