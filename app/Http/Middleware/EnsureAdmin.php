<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Réservé aux comptes administrateurs (role_id 1 ou 2). */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->estAdmin() || ! $user->estActif()) {
            if ($request->expectsJson()) {
                abort(403, 'Accès réservé aux administrateurs.');
            }

            return redirect()->guest(route('login'))->with('error', 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
