<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_admin || ! $user->is_approved) {
            abort(403, 'Tu cuenta no tiene autorización para acceder al panel administrativo.');
        }

        return $next($request);
    }
}
