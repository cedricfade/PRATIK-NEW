<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Espace annonceur immobilier : comptes pro (role 5) ; les admins y ont aussi accès. */
class EnsurePro
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! ($user->estPro() || $user->estAdmin()) || ! $user->estActif()) {
            if ($request->expectsJson()) {
                abort(403, 'Accès réservé aux annonceurs.');
            }

            return redirect()->guest(route('immo.login'))->with('error', 'Connectez-vous avec votre compte annonceur.');
        }

        return $next($request);
    }
}
