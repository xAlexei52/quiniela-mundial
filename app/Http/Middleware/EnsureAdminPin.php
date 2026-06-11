<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege el panel de admin con un PIN simple (sin sistema de usuarios).
 * El PIN válido marca la sesión; mientras dure, se permite el acceso.
 */
class EnsureAdminPin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin_ok')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
